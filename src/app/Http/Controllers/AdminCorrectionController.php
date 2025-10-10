<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Rest;

class AdminCorrectionController extends Controller
{
    public function index(request $request) {
        $tab = $request->query('tab', 'pending-approval');

        $allAttendances = Attendance::with('user', 'latestCorrection')
            ->get();

        $pendingAttendances = $allAttendances
            ->filter(function($attendance) {
                return $attendance->latestCorrection && $attendance->latestCorrection->status === '承認待ち';
            })
            ->sortByDesc('date')
            ->values();

        $approvedAttendances = $allAttendances
            ->filter(function($attendance) {
                return $attendance->latestCorrection && $attendance->latestCorrection->status === '承認済み';
            })
            ->sortByDesc('date')
            ->values();

        return view('admin.corrections', compact('pendingAttendances', 'approvedAttendances', 'tab'));
    }

    public function show($id) {
        $attendance = Attendance::where('id', $id)
            ->with('user')
            ->first();

        $rests = Rest::where('attendance_id', $attendance->id)->get();

        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->latest('applied_at')
            ->first();

        return view('admin.correction-detail', compact('attendance', 'rests', 'correction'));
    }

    public function approved($id) {
        $attendance = Attendance::where('id', $id)->first();

        if (!$attendance) {
            return redirect()->route('corrections.index');
        }

        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('status', '承認待ち')
            ->latest('applied_at')
            ->first();

        if ($correction) {
            $correction->update([
                'status' => '承認済み',
            ]);
        }

        return redirect()->route('corrections.show', ['id' => $id])
            ->with('success', '勤怠のステータスを「承認済み」に変更しました');
    }

}
