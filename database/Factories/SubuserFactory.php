<?php

namespace Database\Factories;

use Pterodactyl\Models\Subuser;
use Pterodactyl\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubuserFactory extends Factory
{
    protected $model = Subuser::class;

    public function definition(): array
    {
        return [
            'permissions' => [
                Permission::ACTION_WEBSOCKET_CONNECT,
            ],
        ];
    }
}
