<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

class CheckOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_clock_out_correctly()
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
            'end_time' => null,
        ]);

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('退勤');

        $this->post(route('attendance.clock-out'));

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('退勤済');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->end_time);
    }

    public function test_can_see_own_attendance_records()
    {
        $user = User::forceCreate([
            'name' => 'テストユーザー',
            'email' => 'test3@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.clock-in'));
        $this->post(route('attendance.clock-out'));

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->end_time);

        $displayStartTime = Carbon::parse($attendance->start_time)->format('H:i');
        $displayEndTime = Carbon::parse($attendance->end_time)->format('H:i');

        $today = now();
        $displayDate = $today->format('m/d');
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek];

        $this->get('/attendance/list')
            ->assertOk()
            ->assertSeeInOrder([
                $displayDate . '(' . $dayOfWeek . ')',
                $displayStartTime,
                $displayEndTime
            ]);
    }
}
