<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Attendance;
use App\Models\Rest;

class AttendanceController extends Controller
{
    public function stamp() {
        $user = Auth::user();
        $today = Carbon::now();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today->toDateString())
            ->first();

        $onBreak = false;
        if ($todayAttendance) {
            $onBreak = Rest::where('attendance_id', $todayAttendance->id)
                ->whereNull('end_time')
                ->exists();
        }

        $status = $this->getStatus($todayAttendance, $onBreak);

        return view('attendance.stamp', compact('today', 'todayAttendance', 'onBreak', 'status'));
    }

    private function getStatus($attendance, $onBreak)
    {
        if (!$attendance) {
            return '勤務外';
        }
        if ($attendance->end_time) {
            return '退勤済';
        }
        if ($onBreak) {
            return '休憩中';
        }
        return '出勤中';
    }
}
