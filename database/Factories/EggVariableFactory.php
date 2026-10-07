<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Pterodactyl\Models\EggVariable;
use Illuminate\Database\Eloquent\Factories\Factory;

class EggVariableFactory extends Factory
{
    protected $model = EggVariable::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->firstName,
            'description' => $this->faker->sentence(),
            'env_variable' => Str::upper(Str::replaceArray(' ', ['_'], $this->faker->words(2, true))),
            'default_value' => $this->faker->colorName,
            'user_viewable' => 0,
            'user_editable' => 0,
            'rules' => 'required|string',
        ];
    }

    public function viewable(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_viewable' => 1,
            ];
        });
    }

    public function editable(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_editable' => 1,
            ];
        });
    }
}
