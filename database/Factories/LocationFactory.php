<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Pterodactyl\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'short' => Str::random(8),
            'long' => Str::random(32),
        ];
    }
}
