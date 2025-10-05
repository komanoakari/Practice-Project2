<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AttendanceController;

Route::middleware('auth')->group(function() {
    Route::get('/attendance', function() {
        return view('attendance.stamp');
    })->name('attendance.stamp');

    Route::get('/attendance/list', function() {
        return view('attendance.index');
    })->name('attendance.index');
});