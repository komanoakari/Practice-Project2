<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserAttendanceController;

Route::middleware(['auth','verified'])->group(function() {
    Route::get('/attendance', [AttendanceController::class, 'stamp'])->name('attendance.stamp');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.break-in');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.break-out');

    Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('attendance.index');

    Route::get('/attendance/detail/{id}', [UserAttendanceController::class, 'detail'])->name('attendance.detail');
});

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
