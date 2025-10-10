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

    public function show($id) {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendance) {
            return redirect()->back()
                ->withErrors(['detail' => '勤怠データがありません']);
        }

        $rests = Rest::where('attendance_id', $attendance->id)->get();

        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->latest('applied_at')
            ->first();

        return view('attendance.detail', compact('attendance', 'rests', 'correction'));
    }

    public function update(UpdateAttendanceRequest $request, $id) {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance.index');
        }

        $breakValidation = $this->validateBreakTimes($request);

        if ($breakValidation!== true) {
            return redirect()->back()
                ->withInput()
                ->withErrors($breakValidation);
        }

        $starts = $request->input('break_starts', []);
        $ends = $request->input('break_ends', []);
        $count = max(count($starts), count($ends));

        DB::transaction(function () use ($attendance, $request, $starts, $ends, $count) {
            $attendance->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
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

            AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'applied_at' => now(),
                'status' => '承認待ち',
                'remarks' => $request->remarks,
            ]);
        });
        return redirect()->route('attendance.detail', $id)
            ->with('success', '勤怠を更新し、「承認待ち」に変更しました');
    }

    private function validateBreakTimes($request)
    {
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        $breakStarts = $request->input('break_starts', []);
        $breakEnds = $request->input('break_ends', []);
        $count = max(count($breakStarts), count($breakEnds));

        for ($i = 0; $i < $count; $i++) {
            $breakStart = $breakStarts[$i] ?? null;
            $breakEnd = $breakEnds[$i] ?? null;

            if (empty($breakStart) && empty($breakEnd)) {
                continue;
            }

            if (empty($breakStart) || empty($breakEnd)) {
                return ['break_error' => '休憩時間が不適切な値です'];
            }

            if ($breakStart < $startTime || $breakStart > $endTime) {
                return ['break_error' => '休憩時間が不適切な値です'];
            }

            if ($breakEnd > $endTime) {
                return ['break_error' => '休憩時間もしくは退勤時間が不適切な値です'];
            }

            if ($breakStart >= $breakEnd) {
                return ['break_error' => '休憩時間が不適切な値です'];
            }
        }

        return true;
    }
}