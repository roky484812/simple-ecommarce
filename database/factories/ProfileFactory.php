<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'avatar_path' => null,
            'bio' => fake()->optional()->sentence(),
            'date_of_birth' => fake()->optional()->date(),
            'gender' => fake()->optional()->randomElement(['male', 'female', 'other']),
        ];
    }
}
