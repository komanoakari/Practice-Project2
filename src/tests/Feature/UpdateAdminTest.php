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
use Illuminate\Support\Facades\Hash;

class UpdateAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_detail_displays_selected_data_for_admin()
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

        $rest = Rest::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $displayYear = now()->format('Y年');
        $displayDate = now()->format('m月d日');
        $startTime = Carbon::parse($attendance->start_time)->format('H:i');
        $endTime = Carbon::parse($attendance->end_time)->format('H:i');
        $breakStartTime = Carbon::parse($rest->start_time)->format('H:i');
        $breakEndTime = Carbon::parse($rest->end_time)->format('H:i');

        $this->get(route('admin.detail', ['id' => $attendance->id]))
            ->assertOk()
            ->assertSee($displayYear)
            ->assertSee($displayDate)
            ->assertSee($attendance->user->name)
            ->assertSee($startTime)
            ->assertSee($endTime)
            ->assertSee($breakStartTime)
            ->assertSee($breakEndTime);
    }

    public function test_shows_error_if_clock_in_after_clock_out()
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
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $this->get(route('admin.detail', ['id' => $attendance->id]))
            ->assertOk();

        $response = $this->from(route('admin.detail', ['id' => $attendance->id]))
            ->post(route('admin.update', ['id' => $attendance->id]), [
                'start_time' => '20:00:00',
                'end_time' => '18:00:00',
                'remarks' => '寝坊',
            ])
            ->assertRedirect(route('admin.detail', ['id' => $attendance->id]))
            ->assertSessionHasErrors(['start_time' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_shows_error_if_break_start_after_clock_out()
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

        Rest::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $this->get(route('admin.detail', ['id' => $attendance->id]))
            ->assertOk();

        $response = $this->from(route('admin.detail', ['id' => $attendance->id]))
            ->post(route('admin.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'break_starts' => ['20:00:00'],
                'break_ends' => ['13:00:00'],
                'remarks' => '寝坊',
            ])
            ->assertRedirect(route('admin.detail', ['id' => $attendance->id]))
            ->assertSessionHasErrors(['break_error' => '休憩時間が不適切な値です']);
    }

    public function test_shows_error_if_break_end_after_clock_out()
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

        Rest::forceCreate([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $this->get(route('admin.detail', ['id' => $attendance->id]))
            ->assertOk();

        $response = $this->from(route('admin.detail', ['id' => $attendance->id]))
            ->post(route('admin.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'break_starts' => ['12:00:00'],
                'break_ends' => ['20:00:00'],
                'remarks' => '寝坊',
            ])
            ->assertRedirect(route('admin.detail', ['id' => $attendance->id]))
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

        $admin = Admin::forceCreate([
            'name' => '管理ユーザー',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admins');

        $this->get(route('admin.detail', ['id' => $attendance->id]))
            ->assertOk();

        $response = $this->from(route('admin.detail', ['id' => $attendance->id]))
            ->post(route('admin.update', ['id' => $attendance->id]), [
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'remarks' => '',
            ])
            ->assertRedirect(route('admin.detail', ['id' => $attendance->id]))
            ->assertSessionHasErrors(['remarks' => '備考を記入してください']);
    }




}
