<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Pterodactyl\Models\Database;
use Illuminate\Database\Eloquent\Factories\Factory;

class DatabaseFactory extends Factory
{
    protected $model = Database::class;

    public function definition(): array
    {
        static $password;

        return [
            'database' => Str::random(10),
            'username' => Str::random(10),
            'remote' => '%',
            'password' => $password ?: encrypt('test123'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
