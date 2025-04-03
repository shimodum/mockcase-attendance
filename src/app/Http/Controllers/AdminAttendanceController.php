<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧画面（管理者）の表示
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $attendances = Attendance::with('user', 'breakTimes')
            ->where('date', $date)
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }

    // 勤怠詳細画面（管理者）の表示
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    // 勤怠修正処理（管理者）
    public function update(AdminAttendanceUpdateRequest $request, Attendance $attendance)
    {
        $validated = $request->validated();

        $attendance->update([
            'date' => $validated['date'],
            'clock_in' => $validated['clock_in'] ? Carbon::parse($validated['date'] . ' ' . $validated['clock_in']) : null,
            'clock_out' => $validated['clock_out'] ? Carbon::parse($validated['date'] . ' ' . $validated['clock_out']) : null,
            'note' => $validated['note'],
        ]);

        // 休憩再登録
        $attendance->breakTimes()->delete();

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
        $user = User::findOrFail($id);

        // 表示対象の月（GETパラメータまたは今月）
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));

        // 前月・翌月の値を計算（ボタン用）
        $parsedMonth = Carbon::createFromFormat('Y-m', $currentMonth);
        $prevMonth = $parsedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $parsedMonth->copy()->addMonth()->format('Y-m');

        // 対象月の勤怠データ取得（breakTimesとuser情報を含めて取得）
        $attendances = Attendance::with('breakTimes', 'user')
            ->where('user_id', $user->id)
            ->whereYear('date', $parsedMonth->year)
            ->whereMonth('date', $parsedMonth->month)
            ->orderBy('date')
            ->get()
            ->map(function ($attendance) {
                // 合計・休憩時間の整形（すでにカラムがある場合はそれを使用）
                $attendance->clock_in_time = optional($attendance->clock_in)->format('H:i');
                $attendance->clock_out_time = optional($attendance->clock_out)->format('H:i');

                // 休憩時間合計の計算
                $totalBreak = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_start && $break->break_end) {
                        $totalBreak += Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start));
                    }
                }

                $attendance->break_duration = floor($totalBreak / 60) . ':' . str_pad($totalBreak % 60, 2, '0', STR_PAD_LEFT);

                // 勤務時間計算
                if ($attendance->clock_in && $attendance->clock_out) {
                    $workingMinutes = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in)) - $totalBreak;
                    $attendance->working_duration = floor($workingMinutes / 60) . ':' . str_pad($workingMinutes % 60, 2, '0', STR_PAD_LEFT);
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
            'nextMonth'
        ));
    }
}
