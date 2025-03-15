<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    // 出勤登録（出勤ボタン押下後の処理、打刻処理）（POST）
    public function store(Request $request)
    {
        // TODO: 打刻処理の実装（出勤・退勤・休憩など）
    }

    // 勤怠一覧表示
    public function index()
    {
        return view('attendance.list');
    }

    // 勤怠詳細表示
    public function show($id)
    {
        return view('attendance.detail', ['id' => $id]);
    }
}
