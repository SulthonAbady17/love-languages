<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
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
            'statement_id' => Statement::factory(),
            'value' => fake()->numberBetween(1, 5),
        ];
    }
}
