<?php

namespace App\Providers;

use App\Contracts\Repositories\RepositoryInterface;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Transaction;
use App\Repositories\BaseRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);

        // Bind specific repositories when they're requested
        $this->app->when(TransactionRepository::class)
            ->needs('$model')
            ->give(function () {
                return new Transaction;
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
