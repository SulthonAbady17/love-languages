<?php

namespace Database\Factories;

use App\Models\LoveLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoveLanguage>
 */
class LoveLanguageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Words of Affirmation',
                'Quality Time',
                'Receiving Gifts',
                'Acts of Service',
                'Physical Touch',
            ]),
            'description' => fake()->sentence(),
        ];
    }
}
