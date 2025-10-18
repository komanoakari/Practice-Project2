<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ];
    }
}
