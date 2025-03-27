<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    //勤怠一覧画面（管理者）の表示
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $attendances = Attendance::with('user', 'breaks') //ユーザー名や休憩時間も一覧で取得
            ->where('date', $date)
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }
}
