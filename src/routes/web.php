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
    Route::get('/login', 'showForm')->name('login');
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

// 勤怠登録関連（一般ユーザー）
Route::middleware(['auth'])->prefix('/attendance')->controller(AttendanceController::class)->group(function () {
    Route::get('/', 'showBefore')->name('attendance.before');            // 出勤前画面（デフォルト）
    Route::post('/', 'store')->name('attendance.store');                 // 勤怠登録処理（打刻）

    Route::get('/working', 'showWorking')->name('attendance.working');   // 出勤後画面
    Route::get('/break', 'showBreak')->name('attendance.break');         // 休憩中画面
    Route::post('/break/start', 'startBreak')->name('attendance.break_start'); // 休憩開始処理
    Route::post('/break/end', 'endBreak')->name('attendance.break_end');       // 休憩終了処理

    Route::post('/clockout', 'clockOut')->name('attendance.clockout');   //退勤処理
    Route::get('/after', 'showAfter')->name('attendance.after');         // 退勤後画面

    Route::get('/list', 'index')->name('attendance.list');               // 勤怠一覧
    Route::get('/{id}', 'show')->name('attendance.detail');              // 勤怠詳細
});

/*
|--------------------------------------------------------------------------
| 修正申請関連（一般ユーザー・管理者 共通）
|--------------------------------------------------------------------------
*/
Route::prefix('/stamp_correction_request')->controller(StampCorrectionRequestController::class)->group(function () {
    Route::get('/list', 'index');
    Route::get('/approve/{attendance_correction_request}', 'showApprove');
    Route::post('/approve/{attendance_correction_request}', 'approve');
});

/*
|--------------------------------------------------------------------------
| ログアウト処理（一般ユーザー・管理者 共通）
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
    Route::get('/list', 'index');
    Route::get('/{id}', 'show');
    Route::get('/staff/{id}', 'staffIndex');
});

// 管理者 スタッフ一覧
Route::get('/admin/staff/list', [AdminStaffController::class, 'index']);
