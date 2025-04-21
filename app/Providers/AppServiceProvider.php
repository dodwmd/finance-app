<?php

namespace App\Providers;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Contracts\Repositories\RecurringTransactionRepositoryInterface;
use App\Contracts\Repositories\RepositoryInterface;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Budget;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Repositories\BaseRepository;
use App\Repositories\BudgetRepository;
use App\Repositories\RecurringTransactionRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register base repository
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);

        // Register Transaction repository with model injection
        $this->app->bind(TransactionRepositoryInterface::class, function ($app) {
            return new TransactionRepository(new Transaction);
        });

        // Register RecurringTransaction repository with model injection
        $this->app->bind(RecurringTransactionRepositoryInterface::class, function ($app) {
            return new RecurringTransactionRepository(new RecurringTransaction);
        });

        // Register Budget repository with model injection
        $this->app->bind(BudgetRepositoryInterface::class, function ($app) {
            return new BudgetRepository(new Budget);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
