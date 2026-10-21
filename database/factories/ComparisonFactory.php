<?php

namespace Database\Factories;

use App\Models\Comparison;
use App\Models\User;
use App\Support\Token;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comparison>
 */
class ComparisonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'initiator_id' => User::factory(),
            'partner_id' => User::factory(),
            'token' => Token::generate(),
        ];
    }
}
