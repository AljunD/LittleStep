<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'guardian_id' => null,
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'sex' => $this->faker->randomElement(['Male','Female']),
            'date_of_birth' => $this->faker->date(),
            'address' => $this->faker->address(),
            'handedness' => $this->faker->randomElement(['right','left','both','not_yet_established']),
            'is_studying' => $this->faker->boolean(),
            'school_name' => $this->faker->company(),
            'fathers_name' => $this->faker->name('male'),
            'fathers_age' => $this->faker->numberBetween(25,60),
            'fathers_occupation' => $this->faker->jobTitle(),
            'fathers_education' => $this->faker->randomElement(['High School','College','Vocational']),
            'mothers_name' => $this->faker->name('female'),
            'mothers_age' => $this->faker->numberBetween(25,60),
            'mothers_occupation' => $this->faker->jobTitle(),
            'mothers_education' => $this->faker->randomElement(['High School','College','Vocational']),
            'number_of_siblings' => $this->faker->numberBetween(0,5),
            'birth_order' => $this->faker->numberBetween(1,5),
            'photo_path' => null,
        ];
    }
}
