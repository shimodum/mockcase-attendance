<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceCorrectionRequest;

class AttendanceController extends Controller
{
    // 出勤前画面の表示
    public function showBefore()
    {
        return view('attendance.before');
    }

    // 出勤後画面の表示
    public function showWorking()
    {
        return view('attendance.working');
    }

    // 休憩中画面の表示
    public function showBreak()
    {
        return view('attendance.break');
    }

    // 退勤後画面の表示
    public function showAfter()
    {
        return view('attendance.after');
    }

    // 出勤登録処理（打刻）（POST）
    public function store(Request $request)
    {
        $user = auth()->user(); // ログイン中のユーザーを取得
        $today = now()->format('Y-m-d');

        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today) // 今日すでに出勤記録があるかチェック(= 同じ日にすでに出勤済みなら重複登録しない)
            ->first();

        if ($existing) {
            return redirect()->route('attendance.working');
        }

        // 新しく出勤レコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => now(),
            'status' => 'waiting_approval', // 初期ステータス
        ]);

        return redirect()->route('attendance.working');
    }

    // 休憩開始処理（POST）
    // → 「休憩入」ボタンを押したときに呼び出される処理
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

        return redirect()->route('attendance.break');
    }

    // 休憩終了処理（POST）
    // → 「休憩戻」ボタンを押したときに呼び出される処理
    public function endBreak(Request $request)
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        // 今日の勤怠レコードを取得
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

        return redirect()->route('attendance.working');
    }

    // 退勤処理（POST）
    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        // 今日の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 退勤時間が未登録の場合のみ更新
        if ($attendance && is_null($attendance->clock_out)) {
            $attendance->update([
                'clock_out' => now(),
            ]);
        }

        return redirect()->route('attendance.after');
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
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m'); // ← routing用
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m'); // ← routing用
        $displayMonth = $currentMonth->format('Y/m'); // ← 表示用


        return view('attendance.list', compact('attendances', 'prevMonth', 'nextMonth', 'displayMonth'));
    }



    // 勤怠詳細表示
    public function show($id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id); //休憩情報も一緒に取得

        return view('attendance.detail', compact('attendance'));
    }

    // 勤怠修正申請の送信処理
    public function requestCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // note（備考）を更新・ステータス変更
        $attendance->note = $request->input('note');
        $attendance->status = 'waiting_approval';
        $attendance->save();

        return redirect()->route('attendance.detail', $attendance->id)
            ->with('message', '修正申請を送信しました。');
    }    
}
