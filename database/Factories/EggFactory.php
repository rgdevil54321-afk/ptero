<?php

namespace Database\Factories;

use Ramsey\Uuid\Uuid;
use Pterodactyl\Models\Egg;
use Illuminate\Database\Eloquent\Factories\Factory;

class EggFactory extends Factory
{
    protected $model = Egg::class;

    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'name' => $this->faker->name,
            'description' => implode(' ', $this->faker->sentences()),
            'startup' => 'java -jar test.jar',
        ];
    }
}
