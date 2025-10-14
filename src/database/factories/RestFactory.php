<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ];
    }
}
