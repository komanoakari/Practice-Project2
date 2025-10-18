<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;

class ListAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_users_attendance_today()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー1',
            'email' => 'test1@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $tester = User::forceCreate([
            'name' => 'テストユーザー2',
            'email' => 'test2@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $userAttendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $testerAttendance = Attendance::forceCreate([
            'user_id' => $tester->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $userStartTime = Carbon::parse($userAttendance->start_time)->format('H:i');
        $userEndTime = Carbon::parse($userAttendance->end_time)->format('H:i');
        $testerStartTime = Carbon::parse($testerAttendance->start_time)->format('H:i');
        $testerEndTime = Carbon::parse($testerAttendance->end_time)->format('H:i');

        $displayDate = now()->format('Y/m/d');

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertSee($displayDate)
            ->assertSee($user->name)
            ->assertSee($userStartTime)
            ->assertSee($userEndTime)
            ->assertSee($tester->name)
            ->assertSee($testerStartTime)
            ->assertSee($testerEndTime);
    }

    public function test_admin_attendance_view_shows_current_date()
    {
        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $displayDate = now()->format('Y/m/d');

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertSee($displayDate);
    }

    public function test_admin_attendance_view_shows_previous_day()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー1',
            'email' => 'test1@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->subDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $previousDayParam = now()->subDay()->format('Y-m-d');
        $previousDayDisplay = now()->subDay()->format('Y/m/d');

        $this->get(route('admin.index', ['date' => $previousDayParam]))
            ->assertOk()
            ->assertSee($previousDayDisplay)
            ->assertSee($user->name)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    public function test_admin_attendance_view_shows_next_day()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー1',
            'email' => 'test1@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $nextDayParam = now()->addDay()->format('Y-m-d');
        $nextDayDisplay = now()->addDay()->format('Y/m/d');

        $this->get(route('admin.index', ['date' => $nextDayParam]))
            ->assertOk()
            ->assertSee($nextDayDisplay)
            ->assertSee($user->name)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }


}
