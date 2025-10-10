<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Attendance;

class UserCorrectionController extends Controller
{
    public function index(Request $request) {
        $tab = $request->query('tab', 'pending-approval');

        $user = Auth::user();

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

        return view('attendance.correction-list', compact('pendingAttendances', 'approvedAttendances', 'tab'));
    }
}
