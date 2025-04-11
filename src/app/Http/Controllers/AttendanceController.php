<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrection;
use App\Models\BreakTimeCorrection;
use App\Http\Requests\AttendanceCorrectionRequest;

class AttendanceController extends Controller
{
    // ユーザーの勤怠ステータスに応じて適切な画面（出勤前・出勤後・休憩中・退勤後）を表示する
    public function show()
    {
        $user = auth()->user(); // ログイン中のユーザー情報を取得
        $today = now()->format('Y-m-d'); // 今日の日付（例: 2025-03-09）を取得

        // 今日のそのユーザーの勤怠記録を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 勤怠記録がまだない → つまり「出勤前」の状態なので、出勤ボタンを表示する画面へ
        if (!$attendance) {
            return view('attendance.before');
        }

        // 退勤後（clock_outが記録されている）
        if ($attendance->clock_out) {
            return view('attendance.after');
        }

        // 勤怠記録はあるけど「退勤していない」場合は、現在休憩中かどうかを確認
        // → break_end が null のレコードがあれば「休憩中」と判断
        $onBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->exists();

        if ($onBreak) {
            return view('attendance.break');
        }

        // 出勤後の通常状態（出勤済みで、退勤もしていなくて、休憩にも入っていない）
        return view('attendance.working');
    }

    // 出勤登録処理（POST）
    public function store(Request $request)
    {
        $user = auth()->user(); // ログイン中のユーザーを取得
        $today = now()->format('Y-m-d');

        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today) // 今日すでに出勤記録があるかチェック(= 同じ日にすでに出勤済みなら重複登録しない)
            ->first();

        if ($existing) {
            return redirect()->route('attendance');
        }

        // 新しく出勤レコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => now(),
            'status' => 'unconfirmed', // 初期ステータス（初期登録されたが、まだ修正申請されていない状態）
        ]);

        return redirect()->route('attendance');
    }

    // 休憩開始処理（POST）
    public function startBreak(Request $request)
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        // 今日の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 勤怠が存在すれば休憩レコードを作成（開始時間のみ）
        if ($attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => now(),
            ]);
        }

        return redirect()->route('attendance');
    }

    // 休憩終了処理（POST）
    public function endBreak(Request $request)
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 勤怠が存在すれば、最新の休憩レコードに終了時刻をセット
        if ($attendance) {
            $break = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest()
                ->first();

            // 未終了の休憩があれば、終了時間を記録
            if ($break) {
                $break->update([
                    'break_end' => now(),
                ]);
            }
        }

        return redirect()->route('attendance');
    }

    // 退勤処理（POST）
    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 退勤時間が未登録の場合のみ更新
        if ($attendance && is_null($attendance->clock_out)) {
            $attendance->update([
                'clock_out' => now(),
            ]);
        }

        return redirect()->route('attendance');
    }

    // 勤怠一覧表示
    public function index(Request $request)
    {
        $user = auth()->user();

        // パラメータがある場合はその月、なければ今月
        $currentMonth = $request->input('month')
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
            : now()->startOfMonth();

        // 当月1日～末日までの範囲
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $currentMonth->copy()->endOfMonth();

        // 勤怠データ取得（その月のみ）
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        // 前月・翌月の値もビューに渡す
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        $displayMonth = $currentMonth->format('Y/m');

        return view('attendance.list', compact('attendances', 'prevMonth', 'nextMonth', 'displayMonth'));
    }

    // 勤怠詳細表示
    public function showDetail($id)
    {
        // 修正：correctionもwith()で取得しておく
        $attendance = Attendance::with('breakTimes', 'correction', 'user')->findOrFail($id);

        // 修正申請がある場合、表示値を申請内容で上書き
        if ($attendance->correction) {
            $attendance->clock_in = $attendance->correction->requested_clock_in;
            $attendance->clock_out = $attendance->correction->requested_clock_out;
            $attendance->note = $attendance->correction->request_reason;
        }

        return view('attendance.detail', compact('attendance'));
    }

    // 勤怠修正申請の送信処理
    public function requestCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        // 勤怠ステータスと備考を更新
        $attendance->update([
            'note' => $request->input('note'),
            'status' => 'waiting_approval',
        ]);

    // 出退・退勤の修正申請
    $correction = AttendanceCorrection::updateOrCreate(
        ['attendance_id' => $attendance->id],
        [
            'requested_clock_in' => $request->input('clock_in'),
            'requested_clock_out' => $request->input('clock_out'),
            'request_reason' => $request->input('note'),
        ]
    );

    // 休憩修正申請（BreakTimeCorrectionの新規作成 or 上書き）
    $break = $attendance->breakTimes->first(); // ← 休憩1件目に限定（必要に応じて複数対応）

    if ($break) {
        BreakTimeCorrection::updateOrCreate(
            ['break_time_id' => $break->id],
            [
                'requested_break_start' => $request->input('break_start'),
                'requested_break_end' => $request->input('break_end'),
                'request_reason' => $request->input('note'),
            ]
        );
    }

    return redirect()->route('attendance.detail', $attendance->id)
        ->with('message', '修正申請を送信しました。');
    }

}
