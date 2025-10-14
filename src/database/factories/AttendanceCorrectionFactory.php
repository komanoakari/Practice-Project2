<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectionFactory extends Factory
{
    public function definition(): array
    {
        $remarksList = [
            '寝坊',
            '電車遅延',
            '早退',
            '体調不良',
        ];

        return [
            'applied_at' => now(),
            'status' => $this->faker->randomElement(['承認待ち', '承認済み']),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'remarks' => $this->faker->randomElement($remarksList),
        ];
    }
}
