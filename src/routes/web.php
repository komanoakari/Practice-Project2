<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\UserCorrectionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AdminCorrectionController;

Route::get('/email/verify', function() {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function(EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました');
})->middleware('auth')->name('verification.send');

Route::middleware(['auth','verified'])->group(function() {
    Route::get('/attendance', [AttendanceController::class, 'stamp'])->name('attendance.stamp');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.break-in');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.break-out');

    Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('attendance.index');

    Route::get('/attendance/detail/{id}', [UserAttendanceController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [UserAttendanceController::class, 'update'])->name('attendance.update');
});

Route::get('/stamp_correction_request/list', [UserCorrectionController::class, 'index'])
    ->middleware('auth.multi')
    ->name('correction.index');

Route::prefix('admin')->group(function() {
    Route::get('login', [LoginController::class, 'index'])
        ->middleware('guest:admins')->name('admin.login');

    Route::post('login', [LoginController::class, 'login'])
        ->middleware('guest:admins');

    Route::middleware('auth:admins')->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');

        Route::get('attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.index');

        Route::get('attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.detail');
        Route::post('attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.update');

        Route::get('staff/list', [AdminUserController::class, 'index'])->name('staff.index');
        Route::get('attendance/staff/{id}', [AdminUserController::class, 'monthly'])->name('staff.monthly');
        Route::get('attendance/staff/export/{id}', [ExportController::class, 'export'])->name('export.monthly');

        Route::get('stamp_correction_request/approve/{id}', [AdminCorrectionController::class, 'show'])->name('corrections.show');
        Route::post('stamp_correction_request/approve/{id}', [AdminCorrectionController::class, 'approved'])->name('corrections.approve');
    });
});



