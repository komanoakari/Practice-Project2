<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;

class ListUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_list_shows_all()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->subDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '17:00:00',
        ]);

        $today = now();
        $todayDisplayDate = $today->format('m/d');
        $todayDayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek];

        $yesterday = now()->subDay();
        $yesterdayDisplayDate = $yesterday->format('m/d');
        $yesterdayDayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$yesterday->dayOfWeek];

        $this->get('/attendance/list')
            ->assertOk()
            ->assertSeeInOrder([
                $todayDisplayDate . '(' . $todayDayOfWeek . ')',
                '09:00',
                '18:00',
            ])
            ->assertSeeInOrder([
                $yesterdayDisplayDate . '(' . $yesterdayDayOfWeek . ')',
                '10:00',
                '17:00',
            ]);
    }

    public function test_attendance_list_shows_current_month()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $today = now();

        $date = $today->format('Y/m');

        $response = $this->get('/attendance/list')
            ->assertOk()
            ->assertSee($date);
    }

    public function test_attendance_list_shows_previous_month()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->subMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $previousMonthParam = now()->subMonth()->format('Y-m');

        $previousMonthDisplay = now()->subMonth()->format('Y/m');

        $previousDay = now()->subMonth();
        $previousDisplayDate = $previousDay->format('m/d');
        $previousDayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$previousDay->dayOfWeek];

        $this->get(route('attendance.index', ['date' => $previousMonthParam]))
            ->assertOk()
            ->assertSee($previousMonthDisplay)
            ->assertSee($previousDisplayDate . '(' . $previousDayOfWeek . ')')
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    public function test_attendance_list_shows_next_month()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        Attendance::forceCreate([
            'user_id' => $user->id,
            'date' => now()->addMonth()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $nextMonthParam = now()->addMonth()->format('Y-m');

        $nextMonthDisplay = now()->addMonth()->format('Y/m');

        $nextDay = now()->addMonth();
        $nextDisplayDate = $nextDay->format('m/d');
        $nextDayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$nextDay->dayOfWeek];

        $this->get(route('attendance.index', ['date' => $nextMonthParam]))
            ->assertOk()
            ->assertSee($nextMonthDisplay)
            ->assertSee($nextDisplayDate . '(' . $nextDayOfWeek . ')')
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    public function test_can_navigate_to_attendance_detail()
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

        $listResponse = $this->get('/attendance/list')
            ->assertOk()
            ->assertSee('詳細');

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk();
    }

}