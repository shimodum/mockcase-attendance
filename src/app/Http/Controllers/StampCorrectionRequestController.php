<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrection;

class StampCorrectionRequestController extends Controller
{
    // 勤怠修正申請の一覧を表示
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 'waiting_approval');

        $corrections = AttendanceCorrection::with(['attendance.user'])
            ->whereHas('attendance', function ($query) use ($user, $status) {
                $query->where('user_id', $user->id)
                      ->where('status', $status);
            })
            ->orderByDesc('id')
            ->get();

        return view('correction_request.list', compact('corrections', 'status'));
    }

    // 勤怠修正申請の詳細を表示
    public function show(AttendanceCorrection $attendance_correction_request)
    {
        // 勤怠とユーザー情報を読み込む（リレーション）
        $attendance_correction_request->load('attendance.user');

        return view('correction_request.request_detail', [
            'correction' => $attendance_correction_request,
        ]);
    }
}
