<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'status' => 'DRAFT',
            'user_id' => User::factory(),
            'currency' => 'EUR',
            'spent_at' => fake()->date(),
            'category' => fake()->randomElement(['MEAL', 'TRAVEL', 'HOTEL', 'OTHER']),
            'receipt_path' => null,
        ];
    }
}
