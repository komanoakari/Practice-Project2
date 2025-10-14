<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;


class AdminUserController extends Controller
{
    public function index() {
        $users = User::all();

        return view('admin.staff', compact('users'));
    }

    public function monthly(Request $request, $id) {
        $user = User::where('id', $id)->first();

        if ($request->date) {
            $date = Carbon::parse($request->date);
        } else {
            $date = Carbon::now();
        }

        $startDate = $date->copy()->startOfMonth()->toDateString();
        $endDate = $date->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::where('user_id', $id)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->orderBy('date')
            ->get();

        foreach ($attendances as $attendance) {
            $breakMinutes = 0;

            $rests = Rest::where('attendance_id', $attendance->id)->get();

            foreach ($rests as $rest) {
                if ($rest->end_time) {
                    $start = Carbon::parse($attendance->date . ' ' . $rest->start_time);
                    $end = Carbon::parse($attendance->date . ' ' . $rest->end_time);
                    $breakMinutes = $breakMinutes + $end->diffInMinutes($start);
                }
            }

            if ($breakMinutes > 0) {
                $hours = floor($breakMinutes / 60);
                $mins = $breakMinutes % 60;
                $attendance->break_time = sprintf('%02d:%02d', $hours, $mins);
            } else {
                $attendance->break_time = '';
            }

            if ($attendance->end_time) {
                $dayStart = Carbon::parse($attendance->date . ' ' . $attendance->start_time);
                $dayEnd = Carbon::parse($attendance->date . ' ' . $attendance->end_time);
                $totalMinutes = $dayEnd->diffInMinutes($dayStart) - $breakMinutes;

                if ($totalMinutes >= 0) {
                    $hours = floor($totalMinutes / 60);
                    $mins = $totalMinutes % 60;
                    $attendance->total_time = sprintf('%02d:%02d', $hours, $mins);
                } else {
                    $attendance->total_time = '';
                }
            } else {
                $attendance->total_time = '';
            }
        }

        $attendancesByDate = [];
        foreach ($attendances as $attendance) {
            $attendancesByDate[$attendance->date] = $attendance;
        }

        $allAttendances = [];
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();
        $days = $start->daysInMonth;

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $start->copy()->addDays($i);
            $dateStr = $currentDate->format('Y-m-d');

            if (isset($attendancesByDate[$dateStr])) {
                $allAttendances[] = $attendancesByDate[$dateStr];
            } else {
                $empty = new \stdClass();
                $empty->id = null;
                $empty->date = $dateStr;
                $empty->start_time = null;
                $empty->end_time = null;
                $empty->break_time = '';
                $empty->total_time = '';
                $allAttendances[] = $empty;
            }
        }
        $attendances = $allAttendances;

        return view('admin.staff-monthly', compact('user','date', 'attendances'));
    }
}
