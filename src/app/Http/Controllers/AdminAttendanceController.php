<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Rest;

use App\Http\Requests\UpdateAttendanceRequest;

class AdminAttendanceController extends Controller
{
    public function index(Request $request) {
        if ($request->date) {
            $date = Carbon::parse($request->date);
        } else {
            $date = Carbon::now();
        }

        $attendances = Attendance::where('date', $date->format('Y-m-d'))
            ->with('user')
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
        return view('admin.index', compact('date', 'attendances'));
    }

    public function show($id) {
        $attendance = Attendance::where('id', $id)
            ->with('user')
            ->first();

        if (!$attendance) {
            return redirect()->back()
                ->withErrors(['detail' => '勤怠データがありません']);
        }

        $rests = Rest::where('attendance_id', $attendance->id)->get();

        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->latest('applied_at')
            ->first();

        return view('admin.detail', compact('attendance', 'rests', 'correction'));
    }

    public function update(UpdateAttendanceRequest $request, $id) {
        $attendance = Attendance::where('id', $id)
            ->first();

        if (!$attendance) {
            return redirect()->route('admin.index');
        }

        $starts = $request->input('break_starts', []);
        $ends = $request->input('break_ends', []);
        $count = max(count($starts), count($ends));

        DB::transaction(function () use ($attendance, $request, $starts, $ends, $count) {
            $attendance->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'remarks' => $request->remarks,
            ]);

            Rest::where('attendance_id', $attendance->id)->delete();

            for ($i = 0; $i < $count; $i++) {
                $breakStart = $starts[$i] ?? null;
                $breakEnd = $ends[$i] ?? null;

                if (!empty($breakStart) && !empty($breakEnd)) {
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $breakStart,
                        'end_time' => $breakEnd,
                    ]);
                }
            }
        });
        return redirect()->route('admin.detail', $id)
            ->with('success', '勤怠を更新しました');
    }
}