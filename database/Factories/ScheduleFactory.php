<?php

namespace Database\Factories;

use Pterodactyl\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
        ];
    }
}
