<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;

use App\Http\Requests\UpdateAttendanceRequest;

class UserAttendanceController extends Controller
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

        $attendanceByDate = [];
        foreach ($attendances as $att) {
            $attendancesByDate[$att->date] = $att;
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

        return view('attendance.index', compact('date', 'attendances'));
    }

    public function show(Request $request, $id) {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendance) {
            return redirect()->back()
                ->withErrors(['detail' => '勤怠データがありません']);
        }

        $rests = Rest::where('attendance_id', $attendance->id)->get();

        $from = $request->query('from');
        $correctionId = $request->query('correction_id');

        if ($correctionId) {
            $correction = AttendanceCorrection::where('id', $correctionId)
                ->where('attendance_id', $attendance->id)
                ->first();
        } else {
            $correction = AttendanceCorrection::where('attendance_id', $attendance->id)
                ->latest('applied_at')
                ->first();
        }

        if ($correction && ($correction->status === '承認待ち' || ($correction->status === '承認済み' && $from === 'correction'))) {
            $rests = RestCorrection::where('correction_id', $correction->id)->get();
        }

        if ($from === 'correction' && $correction && $correction->status === '承認済み') {
            $restCorrections = RestCorrection::where('correction_id', $correction->id)->get();

            $displayAttendance = clone $attendance;
            $displayAttendance->start_time = $correction->start_time;
            $displayAttendance->end_time = $correction->end_time;
            $displayAttendance->remarks = $correction->remarks;

            return view('attendance.detail', compact('displayAttendance', 'rests', 'from', 'correction'))
                ->with('attendance', $displayAttendance);
        }

        return view('attendance.detail', compact('attendance', 'rests', 'correction', 'from'));
    }

    public function update(UpdateAttendanceRequest $request, $id) {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance.index');
        }

        $starts = $request->input('break_starts', []);
        $ends = $request->input('break_ends', []);
        $count = max(count($starts), count($ends));

        DB::transaction(function () use ($attendance, $request, $starts, $ends, $count) {
            $correctionData = AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'applied_at' => now(),
                'status' => '承認待ち',
                'start_time' => $request->start_time,
                'end_time'=> $request->end_time,
                'remarks' => $request->remarks,
            ]);

            for ($i = 0; $i < $count; $i++) {
                $breakStart = $starts[$i] ?? null;
                $breakEnd = $ends[$i] ?? null;

                if (!empty($breakStart) && !empty($breakEnd)) {
                    RestCorrection::create([
                        'correction_id' => $correctionData->id,
                        'start_time' => $breakStart,
                        'end_time' => $breakEnd,
                    ]);
                }
            }
        });
        return redirect()->route('attendance.detail', $id)
            ->with('success', '勤怠修正を申請しました。');
    }
}