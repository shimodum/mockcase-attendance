<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧画面（管理者）の表示：日付指定で全スタッフの勤怠一覧を表示
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $attendances = Attendance::with('user', 'breakTimes')
            ->where('date', $date)
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }

    // 勤怠詳細画面（管理者）の表示：1人の勤怠詳細画面を表示
    public function show($id)
    {
        // 指定IDの勤怠データを、ユーザー・休憩・修正申請と一緒に取得
        $attendance = Attendance::with('user', 'breakTimes', 'correction')->findOrFail($id);

        // 修正申請がある場合は、申請内容で表示値を上書き
        if ($attendance->correction) {
            $attendance->clock_in = $attendance->correction->requested_clock_in;
            $attendance->clock_out = $attendance->correction->requested_clock_out;
            $attendance->note = $attendance->correction->request_reason;
        }

        // 出勤・退勤時刻を「H:i」形式で整形（例: 09:00）
        $attendance->clock_in_time = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : null;
        $attendance->clock_out_time = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : null;

        // 各休憩時間を整形
        foreach ($attendance->breakTimes as $break) {
            $break->start_time = $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : null;
            $break->end_time = $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : null;
        }

        // 詳細画面にデータを渡して表示
        return view('admin.attendance.detail', compact('attendance'));
    }


    // 勤怠修正処理（管理者）
    public function update(AdminAttendanceUpdateRequest $request, Attendance $attendance)
    {
        // 修正申請中のデータは編集不可にする
        if ($attendance->status === 'waiting_approval') {
            return back()->with('error', 'この勤怠は修正申請中のため、編集できません。');
        }
        
        $validated = $request->validated();

        // 勤怠情報を更新（時間はCarbonで結合）
        $attendance->update([
            'date' => $validated['date'],
            'clock_in' => $validated['clock_in'] ? Carbon::parse($validated['date'] . ' ' . $validated['clock_in']) : null,
            'clock_out' => $validated['clock_out'] ? Carbon::parse($validated['date'] . ' ' . $validated['clock_out']) : null,
            'note' => $validated['note'],
        ]);

        // 休憩データは全削除後、必要な分を再登録
        $attendance->breakTimes()->delete();

        // 入力があれば休憩を再登録
        if (isset($validated['breaks'])) {
            foreach ($validated['breaks'] as $break) {
                if (!empty($break['break_start']) && !empty($break['break_end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => Carbon::parse($validated['date'] . ' ' . $break['break_start']),
                        'break_end' => Carbon::parse($validated['date'] . ' ' . $break['break_end']),
                    ]);
                }
            }
        }

        return redirect()->route('admin.attendance.list')->with('success', '勤怠情報を修正しました');
    }


    // スタッフ別の勤怠一覧の表示（管理者）
    public function staffIndex(Request $request, $id)
    {
        // 対象ユーザー取得
        $user = User::findOrFail($id); // 対象のユーザー取得

        // 表示対象の月（GETパラメータ or 今月）
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m')); // 月を取得（例: 2025-04）
        $parsedMonth = Carbon::createFromFormat('Y-m', $currentMonth); // Carbonに変換

        // 前月・翌月の値を計算（ボタン用）
        $prevMonth = $parsedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $parsedMonth->copy()->addMonth()->format('Y-m');
        $displayMonth = $parsedMonth->format('Y/m'); // 表示用の年月（例: 2025/04）

        // 対象月の勤怠データ取得（breakTimesとuser情報を含めて取得）
        $attendances = Attendance::with('breakTimes', 'user')
            ->where('user_id', $user->id)
            ->whereYear('date', $parsedMonth->year)
            ->whereMonth('date', $parsedMonth->month)
            ->orderBy('date')
            ->get()
            ->map(function ($attendance) {
                // 出退勤時間整形
                $attendance->clock_in_time = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : null;
                $attendance->clock_out_time = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null;

                // 休憩時間合計を計算（分単位）
                $totalBreak = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_start && $break->break_end) {
                        $totalBreak += Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start));
                    }
                }

                $attendance->break_duration = sprintf('%d:%02d', floor($totalBreak / 60), $totalBreak % 60);

                // 勤務時間（出退勤-休憩）
                if ($attendance->clock_in && $attendance->clock_out) {
                    $workingMinutes = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in)) - $totalBreak;
                    $attendance->working_duration = sprintf('%d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
                } else {
                    $attendance->working_duration = '0:00';
                }

                return $attendance;
            });

        return view('admin.attendance.staff_list', compact(
            'user',
            'attendances',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'displayMonth'
        ));
    }

    // CSV出力処理
    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id); // 対象のユーザーを取得
        $month = $request->input('month', Carbon::now()->format('Y-m')); // 月指定（なければ今月）
        $parsedMonth = Carbon::createFromFormat('Y-m', $month); // Carbonに変換

        // 対象月の勤怠データ取得
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->whereYear('date', $parsedMonth->year)
            ->whereMonth('date', $parsedMonth->month)
            ->orderBy('date')
            ->get();

        // 出力するCSVファイルの名前を作成
        $filename = $user->name . '_勤怠一覧_' . $parsedMonth->format('Y_m') . '.csv';

        // CSVをストリーム形式でレスポンスする（1行ずつストリーム出力することでメモリ節約）
        $response = new StreamedResponse(function () use ($attendances) {
            // 出力先を標準出力（ダウンロード）に設定
            $stream = fopen('php://output', 'w');

            // Excel向けにBOM付きUTF-8で出力
            fputs($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー行
            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

            // 勤怠データを1行ずつCSVとして出力
            foreach ($attendances as $attendance) {
                // 出勤時刻と退勤時刻を H:i（例: 09:00）形式に整形。なければ「－」
                $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '－';
                $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '－';

                // 休憩時間の合計を分単位で計算
                $totalBreak = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_start && $break->break_end) {
                        $totalBreak += Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start));
                    }
                }
                // 休憩時間の合計を「h:mm」形式に変換（例: 1:30）
                $breakDuration = sprintf('%d:%02d', floor($totalBreak / 60), $totalBreak % 60);

                // 勤務時間の合計（出勤～退勤 － 休憩時間）を計算
                $workDuration = '0:00';
                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in)) - $totalBreak;
                    $workDuration = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                }

                // 1行分の勤怠データをCSVとして出力
                fputcsv($stream, [
                    Carbon::parse($attendance->date)->format('Y/m/d'),
                    $clockIn,
                    $clockOut,
                    $breakDuration,
                    $workDuration
                ]);
            }

            // 書き込み終了
            fclose($stream);
        });

        // HTTPヘッダー設定（CSVとしてダウンロードさせる）
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8'); // CSVとして扱う
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"'); // ファイル名指定

        return $response; // 作成したCSVレスポンスを返す（ダウンロード開始）
    }
}
