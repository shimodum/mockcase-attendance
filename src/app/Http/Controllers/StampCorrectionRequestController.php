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

        // 修正申請を取得（ユーザーの権限に応じて絞り込み）
        $corrections = AttendanceCorrection::with(['attendance.user'])
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                // 一般ユーザーの場合：自分の申請だけを取得
                $query->whereHas('attendance', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->whereHas('attendance', function ($q) use ($status) {
                // 勤怠ステータスが一致するデータだけ取得
                $q->where('status', $status);
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
        $attendance_correction_request->load('attendance.user');

        return view('admin/correction_request/approve_request_detail', [
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
        DB::transaction(function () use ($attendance_correction_request) {
            $attendance = $attendance_correction_request->attendance;

            // Attendanceテーブルに修正内容を反映（申請内容をそのまま上書き）
            $attendance->update([
                'date'        => $attendance_correction_request->date,
                'start_time'  => $attendance_correction_request->start_time,
                'end_time'    => $attendance_correction_request->end_time,
                'note'        => $attendance_correction_request->note,
                'status'      => 'approved',
            ]);

            // 修正申請の状態を「承認済み」にし、管理者コメントも保存
            $attendance_correction_request->update([
                'status' => 'approved',
                'admin_comment' => $request->input('admin_comment'),
            ]);
        });

        return redirect()->route('stamp_correction_request.list')->with('message', '修正申請を承認しました。');
    }
}
