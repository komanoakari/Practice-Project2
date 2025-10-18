<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;

class UserInfoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_users_name_and_email()
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

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $this->get(route('staff.index'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($tester->name)
            ->assertSee($user->email)
            ->assertSee($tester->email);
    }

    public function test_admin_can_view_user_attendance()
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

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $this->get(route('staff.index'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('詳細');

        $displayDate = now()->format('m/d');
        $displayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek];

        $startTime = Carbon::parse($attendance->start_time)->format('H:i');
        $endTime = Carbon::parse($attendance->end_time)->format('H:i');

        $this->get(route('staff.monthly', ['id' => $user->id]))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSeeInOrder([
                $displayDate . '(' . $displayOfWeek . ')',
                $startTime,
                $endTime,
            ]);
    }

    public function test_admin_can_view_previous_month_attendance()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->subMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $previousMonth = now()->subMonth();
        $previousMonthParam = $previousMonth->format('Y-m');
        $previousMonthDisplay = $previousMonth->format('Y/m');

        $displayDate = $previousMonth->format('m/d');
        $displayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$previousMonth->dayOfWeek];

        $startTime = Carbon::parse($attendance->start_time)->format('H:i');
        $endTime = Carbon::parse($attendance->end_time)->format('H:i');

        $this->get(route('staff.monthly', ['id' => $user->id, 'date' => $previousMonthParam]))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($previousMonthDisplay)
            ->assertSeeInOrder([
                $displayDate . '(' . $displayOfWeek . ')',
                $startTime,
                $endTime,
            ]);
    }

    public function test_admin_can_view_next_month_attendance()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->addMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $nextMonth = now()->addMonth();
        $nextMonthParam = $nextMonth->format('Y-m');
        $nextMonthDisplay = $nextMonth->format('Y/m');

        $displayDate = $nextMonth->format('m/d');
        $displayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$nextMonth->dayOfWeek];

        $startTime = Carbon::parse($attendance->start_time)->format('H:i');
        $endTime = Carbon::parse($attendance->end_time)->format('H:i');

        $this->get(route('staff.monthly', ['id' => $user->id, 'date' => $nextMonthParam]))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($nextMonthDisplay)
            ->assertSeeInOrder([
                $displayDate . '(' . $displayOfWeek . ')',
                $startTime,
                $endTime,
            ]);
    }

    public function test_admin_can_navigate_to_attendance_detail()
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

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $displayDate = now()->format('m/d');
        $displayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek];

        $startTime = Carbon::parse($attendance->start_time)->format('H:i');
        $endTime = Carbon::parse($attendance->end_time)->format('H:i');

        $this->get(route('staff.monthly', ['id' => $user->id]))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSeeInOrder([
                $displayDate . '(' . $displayOfWeek . ')',
                $startTime,
                $endTime,
            ]);

        $displayYear = now()->format('Y年');
        $displayDay = now()->format('m月d日');

        $this->get(route('admin.detail', ['id' => $attendance->id]))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($displayYear)
            ->assertSee($displayDay)
            ->assertSee($startTime)
            ->assertSee($endTime);
    }
}
