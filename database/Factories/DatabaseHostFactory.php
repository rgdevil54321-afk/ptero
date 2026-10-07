<?php

namespace Database\Factories;

use Pterodactyl\Models\DatabaseHost;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\Factory;

class DatabaseHostFactory extends Factory
{
    protected $model = DatabaseHost::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->colorName,
            'host' => $this->faker->unique()->ipv4,
            'port' => 3306,
            'username' => $this->faker->colorName,
            'password' => Crypt::encrypt($this->faker->word),
        ];
    }
}
