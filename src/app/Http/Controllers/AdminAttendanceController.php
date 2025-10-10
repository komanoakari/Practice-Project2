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

        $breakValidation = $this->validateBreakTimes($request);

        if ($breakValidation !== true) {
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
                'status' => '管理者により修正済み',
                'remarks' => $request->remarks,
            ]);
        });
        return redirect()->route('admin.detail', $id)
            ->with('success', '勤怠を更新しました');
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