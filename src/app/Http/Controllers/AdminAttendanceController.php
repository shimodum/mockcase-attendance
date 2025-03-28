<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧画面（管理者）の表示
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $attendances = Attendance::with('user', 'breakTimes') //勤怠情報（該当ユーザー1件分）とそのユーザー名、休憩情報も取得
            ->where('date', $date)
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }

    // 勤怠詳細画面（管理者）の表示
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakTimes') //勤怠情報（該当ユーザー1件分）とそのユーザー名、休憩情報も取得
            ->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }
}
