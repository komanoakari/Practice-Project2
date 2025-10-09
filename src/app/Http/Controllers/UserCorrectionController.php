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

        $pendingAttendances = Attendance::where('user_id', $user->id)
            ->whereHas('latestCorrection', function($query) {
                $query->where('status', '承認待ち');
            })
            ->with('latestCorrection')
            ->orderBy('date', 'desc')
            ->get();

        $approvedAttendances = Attendance::where('user_id', $user->id)
            ->whereHas('latestCorrection', function($query) {
                $query->where('status', '承認済み');
            })
            ->with('latestCorrection')
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance.correction-list', compact('pendingAttendances', 'approvedAttendances', 'tab'));
    }
}
