<?php

namespace App\Providers;

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
        // Bind repository interfaces to their implementations
        $this->app->bind(
            'App\Contracts\Repositories\TransactionRepositoryInterface',
            TransactionRepository::class
        );

        $this->app->bind(
            'App\Contracts\Repositories\RecurringTransactionRepositoryInterface',
            RecurringTransactionRepository::class
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
