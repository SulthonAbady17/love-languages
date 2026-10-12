<?php

namespace Database\Factories;

use App\Models\LoveLanguage;
use App\Models\Statement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Statement>
 */
class StatementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'love_language_id' => LoveLanguage::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
