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
            $attendances = Attendance::factory()
                ->count(20)
                ->for($user)
                ->create();

            $targets = $attendances->random(1);

            $targets->each(function (Attendance $attendance) {
                AttendanceCorrection::factory()
                    ->for($attendance, 'attendance')
                    ->has(
                        RestCorrection::factory()->count(1),
                        'restCorrections'
                    )
                    ->create();
            });
        });

        $this->call([
            AdminsSeeder::class,
            UsersSeeder::class,
        ]);
    }
}