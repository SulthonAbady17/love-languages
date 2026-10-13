<?php

namespace Database\Factories;

use App\Models\LoveLanguage;
use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Result>
 */
class ResultFactory extends Factory
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
            'love_language_id' => LoveLanguage::factory(),
            'value' => fake()->randomFloat(2, 1, 5),
        ];
    }
}
