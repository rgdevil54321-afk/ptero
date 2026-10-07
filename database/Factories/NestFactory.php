<?php

namespace Database\Factories;

use Ramsey\Uuid\Uuid;
use Pterodactyl\Models\Nest;
use Illuminate\Database\Eloquent\Factories\Factory;

class NestFactory extends Factory
{
    protected $model = Nest::class;

    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'author' => 'testauthor@example.com',
            'name' => $this->faker->word,
            'description' => null,
        ];
    }
}
