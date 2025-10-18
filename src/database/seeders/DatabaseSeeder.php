<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(6)->create()->each(function (User $user) {
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');

                $attendance = Attendance::factory()
                    ->for($user)
                    ->create([
                        'date' => $date,
                    ]);

                Rest::factory()
                    ->for($attendance)
                    ->create();
            }

            $randomAttendance = $user->attendances()->inRandomOrder()->first();

            if ($randomAttendance) {
                $correction = AttendanceCorrection::factory()
                    ->for($randomAttendance, 'attendance')
                    ->create();

                RestCorrection::factory()
                    ->for($correction, 'correction')
                    ->create();
            }
        });

        $this->call([
            AdminsSeeder::class,
            UsersSeeder::class,
        ]);

        $testUser = User::where('email', 'test@example.com')->first();

        if ($testUser) {
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');

                $attendance = Attendance::factory()
                    ->for($testUser)
                    ->create([
                        'date' => $date,
                    ]);

                Rest::factory()
                    ->for($attendance)
                    ->create();
            }

            $randomAttendance = $testUser->attendances()->inRandomOrder()->first();

            if ($randomAttendance) {
                $correction = AttendanceCorrection::factory()
                    ->for($randomAttendance, 'attendance')
                    ->create();

                RestCorrection::factory()
                    ->for($correction, 'correction')
                    ->create();
            }
        }
    }
}