<?php

namespace Database\Factories\Tenant;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\Squad>
 */
class SquadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'SquadName' => Str::ucfirst(fake()->safeColorName()),
            'SquadFee' => ((string) fake()->randomNumber(1)).'0',
            'SquadKey' => fake()->lexify('????????'),
        ];
    }
}
