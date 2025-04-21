<?php

namespace App\Providers;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Contracts\Repositories\FinancialGoalRepositoryInterface;
use App\Contracts\Repositories\RecurringTransactionRepositoryInterface;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Budget;
use App\Models\FinancialGoal;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Repositories\BudgetRepository;
use App\Repositories\FinancialGoalRepository;
use App\Repositories\RecurringTransactionRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Use closure for TransactionRepository to resolve the model dependency
        $this->app->bind(
            TransactionRepositoryInterface::class,
            function ($app) {
                return new TransactionRepository(new Transaction);
            }
        );

        // Use closure for RecurringTransactionRepository to resolve the model dependency
        $this->app->bind(
            RecurringTransactionRepositoryInterface::class,
            function ($app) {
                return new RecurringTransactionRepository(new RecurringTransaction);
            }
        );

        // Use closure for BudgetRepository to explicitly provide the Budget model
        $this->app->bind(
            BudgetRepositoryInterface::class,
            function ($app) {
                return new BudgetRepository(new Budget);
            }
        );

        // Use closure for FinancialGoalRepository to explicitly provide the FinancialGoal model
        $this->app->bind(
            FinancialGoalRepositoryInterface::class,
            function ($app) {
                return new FinancialGoalRepository(new FinancialGoal);
            }
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
