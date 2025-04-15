<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;

/*
|--------------------------------------------------------------------------
| 一般ユーザー向けルート
|--------------------------------------------------------------------------
*/

// 会員登録
Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showForm');
    Route::post('/register', 'store');
});

// ログイン（一般ユーザー）
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showForm')->name('login');
    Route::post('/login', 'authenticate');
});

// メール認証誘導画面
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

// メール認証リンクをクリックしたときの処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // メール認証を完了させる
    return redirect('/attendance'); // 勤怠画面にリダイレクト
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メールを再送信する処理
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send'); // 過度な連打防止のため、1分間に最大6回までリクエスト可能


// 勤怠登録関連（一般ユーザー）
Route::middleware(['auth'])->prefix('/attendance')->controller(AttendanceController::class)->group(function () {
    Route::get('/', 'show')->name('attendance'); //勤怠登録画面表示
    Route::post('/', 'store')->name('attendance.store'); // 出勤処理
    Route::post('/break/start', 'startBreak')->name('attendance.break_start'); // 休憩開始処理
    Route::post('/break/end', 'endBreak')->name('attendance.break_end'); // 休憩終了処理
    Route::post('/clockout', 'clockOut')->name('attendance.clockout'); // 退勤処理

    Route::get('/list', 'index')->name('attendance.list'); // 勤怠一覧画面表示
    Route::get('/{id}', 'showDetail')->name('attendance.detail'); // 勤怠詳細画面表示
    Route::post('/{id}/correction_request', 'requestCorrection')->name('attendance.correction_request'); // 修正申請処理
});


/*
|--------------------------------------------------------------------------
| 修正申請関連（一般ユーザー/管理者 共通）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('/stamp_correction_request')->controller(StampCorrectionRequestController::class)->group(function () {
    Route::get('/list', 'index')->name('stamp_correction_request.list');
});

/*
|--------------------------------------------------------------------------
| ログアウト処理（一般ユーザー/管理者 共通）
|--------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| 管理者向けルート
|--------------------------------------------------------------------------
*/

// ログイン（管理者）※一般ユーザーと同じLoginControllerを使用
Route::controller(LoginController::class)->group(function () {
    Route::get('/admin/login', 'showForm');
    Route::post('/admin/login', 'authenticate');
});

// 管理者 勤怠関連
Route::prefix('/admin/attendance')->controller(AdminAttendanceController::class)->group(function () {
    Route::get('/list', 'index')->name('admin.attendance.list'); // 勤怠一覧
    Route::get('/{id}', 'show'); // 勤怠詳細
    Route::put('/{attendance}', 'update')->name('admin.attendance.update'); // 勤怠修正（保存）
    Route::get('/staff/{id}', 'staffIndex'); // 特定のスタッフの勤怠一覧
    Route::get('/staff/{id}/export', 'exportCsv'); // CSV出力処理
});

// 管理者 スタッフ一覧
Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);


// 管理者 修正申請承認処理
Route::middleware(['auth', 'can:isAdmin'])->prefix('/stamp_correction_request')->controller(StampCorrectionRequestController::class)->group(function () {
    Route::get('/approve/{attendance_correction_request}', 'showApprove')->name('stamp_correction_request.showApprove');
    Route::post('/approve/{attendance_correction_request}', 'approve')->name('stamp_correction_request.approve');
});
