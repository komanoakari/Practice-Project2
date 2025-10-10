<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;

class ExportController extends Controller
{
    public function export(Request $request, $id) {
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

        $filename = $user->name . '_' . $date->format('Y-m') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                $row = [
                    $attendance->date,
                    $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '',
                    $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '',
                    $attendance->break_time,
                    $attendance->total_time,
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
