<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\Hash;

class AdminAttendanceEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_corrections_displayed_for_admin()
    {
        $user1 = User::forceCreate([
            'name' => 'テストユーザー1',
            'email' => 'test1@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user2 = User::forceCreate([
            'name' => 'テストユーザー2',
            'email' => 'test2@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $attendance1 = Attendance::forceCreate([
            'user_id' => $user1->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::forceCreate([
            'user_id' => $user2->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '17:00:00',
        ]);

        AttendanceCorrection::forceCreate([
            'attendance_id' => $attendance1->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'applied_at' => now(),
            'status' => '承認待ち',
            'remarks' => '寝坊'
        ]);

        AttendanceCorrection::forceCreate([
            'attendance_id' => $attendance2->id,
            'applied_at' => now(),
            'start_time' => '10:00:00',
            'end_time' => '17:00:00',
            'status' => '承認待ち',
            'remarks' => '早退'
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $date1 = Carbon::parse($attendance1->date)->format('Y/m/d');
        $date2 = Carbon::parse($attendance2->date)->format('Y/m/d');

        $this->get(route('correction.index', ['tab' => 'pending-approval']))
            ->assertOk()
            ->assertSeeInOrder([
                $user1->name,
                $date1,
                '寝坊',
            ])
            ->assertSeeInOrder([
                $user2->name,
                $date2,
                '早退',
            ]);
    }

    public function test_approved_corrections_displayed_for_admin()
    {
        $user1 = User::forceCreate([
            'name' => 'テストユーザー1',
            'email' => 'test1@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user2 = User::forceCreate([
            'name' => 'テストユーザー2',
            'email' => 'test2@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $attendance1 = Attendance::forceCreate([
            'user_id' => $user1->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::forceCreate([
            'user_id' => $user2->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '17:00:00',
        ]);

        AttendanceCorrection::forceCreate([
            'attendance_id' => $attendance1->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'applied_at' => now(),
            'status' => '承認済み',
            'remarks' => '寝坊'
        ]);

        AttendanceCorrection::forceCreate([
            'attendance_id' => $attendance2->id,
            'applied_at' => now(),
            'start_time' => '10:00:00',
            'end_time' => '17:00:00',
            'status' => '承認済み',
            'remarks' => '早退'
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $date1 = Carbon::parse($attendance1->date)->format('Y/m/d');
        $date2 = Carbon::parse($attendance2->date)->format('Y/m/d');

        $this->get(route('correction.index', ['tab' => 'approved']))
            ->assertOk()
            ->assertSeeInOrder([
                $user1->name,
                $date1,
                '寝坊',
            ])
            ->assertSeeInOrder([
                $user2->name,
                $date2,
                '早退',
            ]);
    }

    public function test_correction_detail_displays_correctly()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $correction = AttendanceCorrection::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '17:00:00',
            'applied_at' => now(),
            'status' => '承認待ち',
            'remarks' => '寝坊'
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $date = Carbon::parse($attendance->date)->format('Y/m/d');

        $this->get(route('correction.index', ['tab' => 'pending-approval']))
            ->assertOk()
            ->assertSeeInOrder([
                $user->name,
                $date,
                '寝坊',
                '詳細',
            ]);

        $this->get(route('corrections.show', ['id' => $correction->attendance_id]))
            ->assertOk()
            ->assertSee('10:00')
            ->assertSee('17:00')
            ->assertSee('寝坊');
    }

    public function test_correction_approval_process_works()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $correction = AttendanceCorrection::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '17:00:00',
            'applied_at' => now(),
            'status' => '承認待ち',
            'remarks' => '寝坊'
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $this->get(route('corrections.show', ['id' => $correction->attendance_id]))
            ->assertOk();

        $this->post(route('corrections.approve', ['id' => $attendance->id]))
            ->assertRedirect(route('corrections.show', ['id' => $attendance->id]));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '17:00:00',
            'remarks' => '寝坊',
        ]);

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->attendance_id,
            'status' => '承認済み',
        ]);
    }

}