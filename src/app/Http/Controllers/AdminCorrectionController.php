<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;

class AdminCorrectionController extends Controller
{
    public function index(request $request) {
        $tab = $request->query('tab', 'pending-approval');

        $pendingCorrections = AttendanceCorrection::where('status', '承認待ち')
            ->with(['attendance.user'])
            ->orderBy('applied_at', 'desc')
            ->get();

        $approvedCorrections = AttendanceCorrection::where('status', '承認済み')
            ->with(['attendance.user'])
            ->orderBy('applied_at', 'desc')
            ->get();

        return view('admin.corrections', compact('pendingCorrections', 'approvedCorrections', 'tab'));
    }

    public function show(Request $request, $id) {
        $attendance = Attendance::where('id', $id)
            ->with('user')
            ->first();

        if (!$attendance) {
            return redirect()->route('correction.index');
        }

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

        if ($correction) {
            $restCorrections = RestCorrection::where('correction_id', $correction->id)->get();
        } else {
            $restCorrections = collect();
        }

        return view('admin.correction-detail', compact('attendance', 'correction', 'restCorrections'));
    }

    public function approved($id) {
        $attendance = Attendance::where('id', $id)->first();

        if (!$attendance) {
            return redirect()->route('correction.index');
        }

        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('status', '承認待ち')
            ->latest('applied_at')
            ->first();

        if ($correction) {
            DB::transaction(function () use ($attendance, $correction) {
                $attendance->update([
                    'start_time' => $correction->start_time,
                    'end_time' => $correction->end_time,
                    'remarks' => $correction->remarks,
                ]);

                Rest::where('attendance_id', $attendance->id)->delete();

                $restCorrections = RestCorrection::where('correction_id', $correction->id)->get();

                foreach ($restCorrections as $RestCorrection) {
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $RestCorrection->start_time,
                        'end_time' => $RestCorrection->end_time,
                    ]);
                }

                $correction->update([
                    'status' => '承認済み',
                ]);
            });
        }

        return redirect()->route('corrections.show', ['id' => $id])
            ->with('success', '勤怠修正を承認しました');
    }
}
