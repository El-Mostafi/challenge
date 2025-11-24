<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = User::where('role', 'EMPLOYEE')->get();

        $categories = ['MEAL', 'TRAVEL', 'HOTEL', 'OTHER'];

        foreach (range(1, 10) as $i) {
            $user = $employees->random();

            Expense::create([
                'user_id' => $user->id,
                'title' => "Test expense $i",
                'amount' => rand(10, 200),
                'currency' => 'EUR',
                'spent_at' => now()->subDays(rand(1, 30)),
                'category' => Arr::random($categories),
                'receipt_path' => null,
                'status' => 'DRAFT',
            ]);
        }
    }
}
