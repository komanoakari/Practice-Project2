<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('出勤');

        $this->post(route('attendance.clock-in'));

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_stamp_once()
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

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('お疲れ様でした。')
            ->assertDontSeeText('出勤');
    }

    public function test_user_can_check_clock_in()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.clock-in'));

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->start_time);

        $displayTime = Carbon::parse($attendance->start_time)->format('H:i');

        $today = now();
        $displayDate = $today->format('m/d');
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek];

        $this->get('/attendance/list')
            ->assertOk()
            ->assertSeeInOrder([
                $displayDate . '(' . $dayOfWeek . ')',
                $displayTime
            ]);
    }

}
