<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    // 勤怠修正申請の一覧を表示（一般ユーザー、管理者共通）
    // 一般ユーザー：自分が申請した修正申請のみ表示、 管理者：すべての申請を表示
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 'waiting_approval'); // クエリパラメータからstatusを取得（デフォルト：waiting_approval）

        // statusが waiting_approval なら attendances テーブルのステータスではなく、
        // 修正申請があることを条件に取得する
        $corrections = AttendanceCorrection::with(['attendance.user'])
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                // 一般ユーザーの場合：自分の申請だけを取得
                $query->whereHas('attendance', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->when($status === 'waiting_approval', function ($query) {
                // 勤怠のステータスが waiting_approval のみ（申請中）
                $query->whereHas('attendance', function ($q) {
                    $q->where('status', 'waiting_approval');
                });
            })
            ->when($status === 'approved', function ($query) {
                // 勤怠のステータスが approved のみ（承認済み）
                $query->whereHas('attendance', function ($q) {
                    $q->where('status', 'approved');
                });
            })
            ->orderByDesc('id')
            ->get();

        return view('correction_request.list', compact('corrections', 'status'));
    }

    // 勤怠修正申請の詳細を表示（一般ユーザー用）
    public function show(AttendanceCorrection $attendance_correction_request)
    {
        // 関連する勤怠情報とユーザー情報も一緒に取得（リレーション）
        $attendance_correction_request->load('attendance.user');

        return view('correction_request.request_detail', [
            'correction' => $attendance_correction_request,
        ]);
    }

    // 修正申請承認画面を表示（管理者専用）
    public function showApprove(AttendanceCorrection $attendance_correction_request)
    {
        // 管理者以外はアクセス禁止（403エラー）
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // 関連する勤怠情報とユーザー情報を取得
        $attendance_correction_request->load('attendance.user', 'attendance.breakTimes.correction');

        return view('admin.correction_request.approve_request', [
            'correction' => $attendance_correction_request,
        ]);
    }

    // 修正申請の承認処理（管理者専用）
    public function approve(Request $request, AttendanceCorrection $attendance_correction_request)
    {
        // 管理者以外はアクセス禁止（403エラー）
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // 処理をトランザクションでまとめる（どちらか失敗したらロールバック）
        DB::transaction(function () use ($attendance_correction_request, $request) {
            $attendance = $attendance_correction_request->attendance;

            // Attendanceテーブルに修正内容を反映（申請内容をそのまま上書き）
            $attendance->update([
                'clock_in' => $attendance_correction_request->requested_clock_in,
                'clock_out' => $attendance_correction_request->requested_clock_out,
                'note' => $attendance_correction_request->request_reason,
                'status' => 'approved',
            ]);

            // 各休憩申請を反映
            foreach ($attendance->breakTimes as $break) {
                if ($break->correction) {
                    $break->update([
                        'break_start' => $break->correction->requested_break_start,
                        'break_end' => $break->correction->requested_break_end,
                    ]);

                    // 申請状態を更新（もしステータスがある場合）
                    $break->correction->update([
                        'status' => 'approved',
                    ]);
                }
            }

            // 勤怠修正申請の状態を更新
            $attendance_correction_request->update([
                'status' => 'approved',
                'admin_comment' => $request->input('admin_comment'),
            ]);
        });

    // 再表示時に「承認済み」と表示させるためにフラッシュメッセージを渡す
    return redirect()
        ->route('stamp_correction_request.showApprove', $attendance_correction_request->id)
        ->with('approved', true);
    }
}
