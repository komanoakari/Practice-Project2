<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;

class UserCorrectionController extends Controller
{
    public function index(Request $request) {
        if (Auth::guard('admins')->check()) {
            return app(AdminCorrectionController::class)->index($request) ;
        }

        $tab = $request->query('tab', 'pending-approval');

        $user = Auth::user();

        $pendingAttendances = Attendance::where('user_id', $user->id)
            ->whereHas('corrections', function($query) {
                $query->where('status', '承認待ち');
            })
            ->with(['user'])
            ->orderBy('date', 'desc')
            ->get();

        foreach ($pendingAttendances as $attendance) {
            $attendance->latestCorrection = AttendanceCorrection::where('attendance_id', $attendance->id)
                ->where('status', '承認待ち')
                ->latest('applied_at')
                ->first();
        }

        $approvedCorrections = AttendanceCorrection::where('status', '承認済み')
            ->whereHas('attendance', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['attendance.user'])
            ->orderBy('applied_at', 'desc')
            ->get();

        return view('attendance.correction-list', compact('pendingAttendances', 'approvedCorrections', 'tab'));
    }
}
