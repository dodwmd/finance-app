<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user if none exists
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        // Create 50 sample transactions for the test user
        Transaction::factory()
            ->count(50)
            ->state(['user_id' => $user->id])
            ->create();

        // Create additional users with transactions
        User::factory()
            ->count(3)
            ->create()
            ->each(function ($user) {
                Transaction::factory()
                    ->count(20)
                    ->state(['user_id' => $user->id])
                    ->create();
            });
    }
}
