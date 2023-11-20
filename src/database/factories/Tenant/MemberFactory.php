<?php

namespace Database\Factories\Tenant;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fake = fake();

        $gender = rand(0, 1) === 0 ? 'Male' : 'Female';

        return [
            'MForename' => $fake->firstName(strtolower($gender)),
            'MSurname' => $fake->lastName(),
            'DateOfBirth' => $fake->dateTimeInInterval('-24 years', '-8 years'),
            'Gender' => $gender,
            'ASANumber' => $fake->randomNumber(7, true),
            'GenderIdentity' => $gender,
            'GenderPronouns' => $gender == 'Male' ? 'He/Him/His' : 'She/Her/Hers',
            'GenderDisplay' => $fake->boolean(),
        ];
    }
}
