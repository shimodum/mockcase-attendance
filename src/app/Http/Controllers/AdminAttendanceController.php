<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
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
}
