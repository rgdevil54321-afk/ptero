<?php

namespace Database\Factories;

use Pterodactyl\Models\ServerTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerTransferFactory extends Factory
{
    protected $model = ServerTransfer::class;

    public function definition()
    {
        return [
            'old_additional_allocations' => [],
            'new_additional_allocations' => [],
            'successful' => null,
            'archived' => false,
        ];
    }
}
