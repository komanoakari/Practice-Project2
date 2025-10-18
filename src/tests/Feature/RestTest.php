<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

class RestTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_break_in()
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
            ->assertSee('休憩入');

        $this->post(route('attendance.break-in'));

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('休憩中');

        $this->assertDatabaseHas('rests', [
            'attendance_id' => $attendance->id,
            'end_time' => null,
        ]);
    }

    public function test_user_can_break_in_many_times()
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

        $this->post(route('attendance.break-in'));
        $this->post(route('attendance.break-out'));

        $rest = Rest::where('attendance_id', $attendance->id)
            ->first();

        $this->assertNotNull($rest);
        $this->assertNotNull($rest->start_time);
        $this->assertNotNull($rest->end_time);

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('休憩入');
    }

    public function test_user_can_break_out()
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

        $this->post(route('attendance.break-in'));
        $this->get('/attendance')
            ->assertOk()
            ->assertSee('休憩戻');

        $this->post(route('attendance.break-out'));

        $rest = Rest::where('attendance_id', $attendance->id)
            ->first();

        $this->assertNotNull($rest);
        $this->assertNotNull($rest->start_time);
        $this->assertNotNull($rest->end_time);

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('出勤中');
    }

    public function test_user_can_break_out_many_times()
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

        $this->post(route('attendance.break-in'));
        $this->post(route('attendance.break-out'));

        $rest = Rest::where('attendance_id', $attendance->id)
            ->first();

        $this->assertNotNull($rest);
        $this->assertNotNull($rest->start_time);
        $this->assertNotNull($rest->end_time);

        $this->post(route('attendance.break-in'));

        $rest = Rest::where('attendance_id', $attendance->id)
            ->orderByDesc('start_time')
            ->first();

        $this->assertEquals(2, Rest::where('attendance_id', $attendance->id)->count());

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('休憩戻');
    }

    public function test_can_view_attendance_list_with_clock_in_time()
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
            'end_time' => '',
        ]);

        Rest::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $startTime = Carbon::parse($attendance->start_time)->format('H:i');

        $breakStart = Carbon::parse($attendance->date . ' 12:00:00');
        $breakEnd = Carbon::parse($attendance->date . ' 13:00:00');
        $breakMinutes = $breakEnd->diffInMinutes($breakStart);

        $hours = floor($breakMinutes / 60);
        $mins = $breakMinutes % 60;
        $expectedBreakTime = sprintf('%02d:%02d', $hours, $mins);

        $today = now();
        $displayDate = $today->format('m/d');
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek];

        $this->get('/attendance/list')
            ->assertOk()
            ->assertSeeInOrder([
                $displayDate . '(' . $dayOfWeek . ')',
                $startTime,
                '',
                $expectedBreakTime,
            ]);
    }
}
