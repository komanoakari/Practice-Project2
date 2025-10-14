<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-1 month', 'now');

        return [
            'date' => $date->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ];
    }
}
