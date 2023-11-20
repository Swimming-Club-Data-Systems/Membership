<?php

namespace Database\Factories\Tenant;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fake = fake();

        return [
            'Forename' => $fake->firstName(),
            'Surname' => $fake->lastName(),
            'EmailAddress' => $fake->unique()->safeEmail(),
            'Password' => '$2a$20$6me7Tp5FRCFbSoHm/swSJORbVSFWw2YlqHzUritiKsdwQddg7pDGq', // password
            'Mobile' => $fake->e164PhoneNumber(),
            'EmailComms' => true,
            'MobileComms' => true,
        ];
    }
}
