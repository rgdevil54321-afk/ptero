<?php

namespace Database\Factories;

use Pterodactyl\Models\Server;
use Pterodactyl\Models\Allocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AllocationFactory extends Factory
{
    protected $model = Allocation::class;

    public function definition(): array
    {
        return [
            'ip' => $this->faker->unique()->ipv4,
            'port' => $this->faker->unique()->numberBetween(1024, 65535),
        ];
    }

    public function forServer(Server $server): self
    {
        return $this->for($server)->for($server->node);
    }
}
