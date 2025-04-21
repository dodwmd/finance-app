<?php

namespace App\Providers;

use App\Contracts\Repositories\RepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\TransactionRepository;
use App\Models\Transaction;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
