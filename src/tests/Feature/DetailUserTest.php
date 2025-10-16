<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

class DetailUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_detail_shows_user_name()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test@gmail.com',
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
            ->assertOk()
            ->assertSee($user->name);
    }

    public function test_attendance_detail_shows_selected_date()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test@gmail.com',
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

        $today = now();
        $displayYear = $today->format('Y年');
        $displayDate = $today->format('m月d日');

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk()
            ->assertSee($displayYear)
            ->assertSee($displayDate);
    }

    public function test_attendance_detail_displays_correct_check_in_and_check_out_times()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test@gmail.com',
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

        $displayStartTime = Carbon::parse($attendance->start_time)->format('H:i');
        $displayEndTime = Carbon::parse($attendance->end_time)->format('H:i');

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk()
            ->assertSee('出勤')
            ->assertSee('退勤')
            ->assertSee($displayStartTime)
            ->assertSee($displayEndTime);
    }

    public function test_attendance_detail_displays_break_times()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test@gmail.com',
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

        $rest = Rest::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $breakStartTime = Carbon::parse($rest->start_time)->format('H:i');
        $breakEndTime = Carbon::parse($rest->end_time)->format('H:i');

        $this->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertOk()
            ->assertSee('休憩')
            ->assertSee($breakStartTime)
            ->assertSee($breakEndTime);
    }


}
