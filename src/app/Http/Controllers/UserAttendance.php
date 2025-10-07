<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Attendance;
use App\Models\Rest;


class UserAttendance extends Controller
{
    public function index(Request $request) {
        $user = Auth::user();

        if ($request->date) {
            $date = Carbon::parse($request->date);
        } else {
            $date = Carbon::now();
        }

        $startDate = $date->copy()->startOfMonth()->toDateString();
        $endDate = $date->copy()->endOfMonth()->toDateString();


        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->orderBy('date')
            ->get();

        foreach ($attendances as $attendance) {
            $breakMinutes = 0;

            $rests = Rest::where('attendance_id', $attendance->id)->get();

            foreach ($rests as $rest) {
                if ($rest->end_time) {
                    $start = Carbon::parse($rest->start_time);
                    $end = Carbon::parse($rest->end_time);
                    $breakMinutes = $breakMinutes + $end->diffInMinutes($start);
                }
            }
            $attendance->break_time = $breakMinutes;

            if ($attendance->end_time) {
                $dayStart = Carbon::parse($attendance->start_time);
                $dayEnd = Carbon::parse($attendance->end_time);
                $totalMinutes = $dayEnd->diffInMinutes($dayStart);
                $attendance->total_time = $totalMinutes - $breakMinutes;
            } else {
                $attendance->total_time = 0;
            }
        }
        return view('attendance.index', compact('date', 'attendances'));
    }
}
