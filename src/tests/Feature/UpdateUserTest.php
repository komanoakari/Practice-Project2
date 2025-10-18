<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\Hash;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_error_if_clock_in_after_clock_out()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk();

        $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.update', ['id' => $attendance->id]), [
                'start_time' => '20:00:00',
                'end_time' => '18:00:00',
                'remarks' => '寝坊',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $attendance->id]))
            ->assertSessionHasErrors(['start_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_shows_error_if_break_start_after_clock_out()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Rest::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk();

        $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'break_starts' => ['20:00:00'],
                'break_ends' => ['13:00:00'],
                'remarks' => '寝坊',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $attendance->id]))
            ->assertSessionHasErrors(['break_error' => '休憩時間が不適切な値です']);
    }

    public function test_shows_error_if_break_end_after_clock_out()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Rest::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk();

        $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'break_starts' => ['12:00:00'],
                'break_ends' => ['20:00:00'],
                'remarks' => '寝坊',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $attendance->id]))
            ->assertSessionHasErrors(['break_error' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    public function test_shows_error_if_remarks_empty()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk();

        $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'remarks' => '',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $attendance->id]))
            ->assertSessionHasErrors(['remarks' => '備考を記入してください']);
    }

    public function test_submit_attendance_correction()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk();

        $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'remarks' => '早退',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        $this->post(route('logout'));

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $displayDate = Carbon::parse($attendance->date)->format('Y/m/d');

        $this->get('/stamp_correction_request/list')
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($displayDate)
            ->assertSee('早退');
    }

    public function test_pending_corrections_displayed_for_user()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $todayAttendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $yesterdayAttendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->subDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->from(route('attendance.detail', ['id' => $todayAttendance->id]))
            ->post(route('attendance.update', ['id' => $todayAttendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'break_starts' => [],
                'break_ends' => [],
                'remarks' => '早退',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $todayAttendance->id]));

        $this->from(route('attendance.detail', ['id' => $yesterdayAttendance->id]))
            ->post(route('attendance.update', ['id' => $yesterdayAttendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'break_starts' => [],
                'break_ends' => [],
                'remarks' => '体調不良',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $yesterdayAttendance->id]));

        $todayDisplayDate = now()->format('Y/m/d');
        $yesterdayDisplayDate = now()->subDay()->format('Y/m/d');

        $this->get('/stamp_correction_request/list')
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($todayDisplayDate)
            ->assertSee($yesterdayDisplayDate)
            ->assertSee('早退')
            ->assertSee('体調不良');
    }

    public function test_approved_corrections_displayed_for_user()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'break_starts' => [],
                'break_ends' => [],
                'remarks' => '早退',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)
                ->where('status', '承認待ち')
                ->first();

        $this->assertNotNull($correction);

        $this->post(route('logout'));

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $this->post(route('corrections.approve', ['id' => $attendance->id]));

        $this->post(route('admin.logout'));

        $this->actingAs($user);

        $displayDate = Carbon::parse($attendance->date)->format('Y/m/d');

        $this->get(route('correction.index', ['tab' => 'approved']))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($displayDate)
            ->assertSee('早退');
    }

    public function test_correction_detail_navigates_to_attendance_detail()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->from(route('attendance.detail', ['id' => $attendance->id]))
            ->post(route('attendance.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'break_starts' => [],
                'break_ends' => [],
                'remarks' => '早退',
            ])
            ->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        $displayDate = Carbon::parse($attendance->date)->format('Y/m/d');

        $this->get(route('correction.index', ['tab' => 'pending-approval']))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($displayDate)
            ->assertSee('早退')
            ->assertSee('詳細');

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('09:00')
            ->assertSee('17:00')
            ->assertSee('早退');
    }
}
