<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
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
    public function update(Request $request, Attendance $attendance)
    {
        // バリデーション（詳細なルールは後でフォームリクエストに移行）
        $validated = $request->validate([
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i', 'after_or_equal:breaks.*.break_start'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        // 勤怠本体の更新
        $attendance->update([
            'clock_in' => $validated['clock_in'] ? Carbon::parse($attendance->date . ' ' . $validated['clock_in']) : null,
            'clock_out' => $validated['clock_out'] ? Carbon::parse($attendance->date . ' ' . $validated['clock_out']) : null,
            'note' => $validated['note'],
        ]);

        // 既存の休憩を一旦削除して再登録（簡易的な処理）
        $attendance->breakTimes()->delete();

        if (isset($validated['breaks'])) {
            foreach ($validated['breaks'] as $break) {
                if (!empty($break['break_start']) && !empty($break['break_end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => Carbon::parse($attendance->date . ' ' . $break['break_start']),
                        'break_end' => Carbon::parse($attendance->date . ' ' . $break['break_end']),
                    ]);
                }
            }
        }

        return redirect()->route('admin.attendance.list')->with('success', '勤怠情報を修正しました');
    }
}
