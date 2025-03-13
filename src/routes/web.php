<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;

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
    Route::get('/login', 'showForm');
    Route::post('/login', 'authenticate');
});

// メール認証
Route::get('/email/verify', function () {
    return view('auth.verify');
});
Route::get('/email/verify/{id}/{hash}', function () {
    // TODO: コントローラーに移行予定
});
Route::post('/email/verification-notification', function () {
    // TODO: コントローラーに移行予定
});

// 勤怠登録・一覧・詳細（一般ユーザー）
Route::prefix('/attendance')->controller(AttendanceController::class)->group(function () {
    Route::post('/', 'store');
    Route::get('/list', 'index');
    Route::get('/{id}', 'show');
});


/*
|--------------------------------------------------------------------------
| 修正申請関連（一般ユーザー・管理者共通）
|--------------------------------------------------------------------------
*/
Route::prefix('/stamp_correction_request')->controller(StampCorrectionRequestController::class)->group(function () {
    Route::get('/list', 'index');
    Route::get('/approve/{attendance_correction_request}', 'showApprove');
    Route::post('/approve/{attendance_correction_request}', 'approve');
});


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
    Route::get('/list', 'index');
    Route::get('/{id}', 'show');
    Route::get('/staff/{id}', 'staffIndex');
});

// 管理者 スタッフ一覧
Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);
