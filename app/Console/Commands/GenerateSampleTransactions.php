<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class GenerateSampleTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sample-transactions {--user=} {--count=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample transactions for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');
        $count = (int) $this->option('count');
        
        if (!$userId) {
            $user = User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password'),
                ]
            );
            $userId = $user->id;
            $this->info("Using user: {$user->name} (ID: {$userId})");
        } else {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return 1;
            }
            $this->info("Using user: {$user->name} (ID: {$userId})");
        }
        
        $categories = [
            'income' => ['Salary', 'Freelance', 'Investment', 'Gift', 'Refund'],
            'expense' => ['Food', 'Transport', 'Housing', 'Entertainment', 'Utilities', 'Shopping', 'Health', 'Education'],
            'transfer' => ['To Savings', 'To Checking', 'To Investment']
        ];
        
        $this->info("Generating {$count} sample transactions...");
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();
        
        for ($i = 0; $i < $count; $i++) {
            $type = array_rand($categories);
            $category = $categories[$type][array_rand($categories[$type])];
            
            $amount = match ($type) {
                'income' => rand(100, 5000) / 100 * 100,
                'expense' => rand(5, 1000) / 100 * 100,
                'transfer' => rand(50, 2000) / 100 * 100,
            };
            
            $randomDays = rand(0, 90);
            $transactionDate = Carbon::now()->subDays($randomDays);
            
            Transaction::create([
                'user_id' => $userId,
                'description' => $this->generateDescription($type, $category),
                'amount' => $amount,
                'type' => $type,
                'category' => $category,
                'transaction_date' => $transactionDate,
            ]);
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        $this->info("Successfully generated {$count} sample transactions for user ID: {$userId}");
        
        return 0;
    }
    
    /**
     * Generate a realistic description based on type and category
     */
    protected function generateDescription(string $type, string $category): string
    {
        $descriptions = [
            'Salary' => ['Monthly Salary', 'Bonus Payment', 'Overtime Pay'],
            'Freelance' => ['Client Project', 'Consulting Fee', 'Contract Work'],
            'Investment' => ['Dividend Payment', 'Stock Sale', 'Interest Income'],
            'Gift' => ['Birthday Gift', 'Holiday Gift', 'Family Support'],
            'Refund' => ['Product Return', 'Service Refund', 'Tax Refund'],
            
            'Food' => ['Grocery Shopping', 'Restaurant Meal', 'Coffee Shop', 'Food Delivery'],
            'Transport' => ['Fuel', 'Public Transport', 'Taxi Ride', 'Parking Fee'],
            'Housing' => ['Rent Payment', 'Mortgage', 'House Repairs', 'Furniture'],
            'Entertainment' => ['Movie Tickets', 'Concert', 'Streaming Service', 'Gaming'],
            'Utilities' => ['Electricity Bill', 'Water Bill', 'Internet Bill', 'Phone Bill'],
            'Shopping' => ['Clothing Purchase', 'Electronics', 'Home Goods', 'Online Shopping'],
            'Health' => ['Doctor Visit', 'Medication', 'Gym Membership', 'Health Insurance'],
            'Education' => ['Course Fee', 'Textbooks', 'Tuition Payment', 'Workshop'],
            
            'To Savings' => ['Transfer to Savings', 'Emergency Fund Deposit', 'Monthly Savings'],
            'To Checking' => ['Transfer to Checking', 'Bill Payment Transfer', 'Regular Transfer'],
            'To Investment' => ['Investment Deposit', 'Retirement Contribution', 'Stock Purchase'],
        ];
        
        $options = $descriptions[$category] ?? ["Payment for {$category}"];
        return $options[array_rand($options)];
    }
}
