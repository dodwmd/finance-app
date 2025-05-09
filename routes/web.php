<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialGoalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Transaction routes
    Route::resource('transactions', TransactionController::class);

    // Recurring Transaction routes
    Route::resource('recurring-transactions', RecurringTransactionController::class);
    Route::patch('/recurring-transactions/{recurringTransaction}/toggle-status', [RecurringTransactionController::class, 'toggleStatus'])
        ->name('recurring-transactions.toggle-status');

    // Category routes
    Route::resource('categories', CategoryController::class);
    Route::post('/api/categories', [CategoryController::class, 'storeApi'])->name('categories.store.api');
    Route::get('/api/categories/{type?}', [CategoryController::class, 'getByType'])->name('categories.by.type');

    // Budget routes
    Route::resource('budgets', BudgetController::class);
    Route::get('/budgets/{budget}/progress', [BudgetController::class, 'showProgress'])->name('budgets.progress');

    // Financial Goal routes
    Route::resource('goals', FinancialGoalController::class);
    Route::get('/goals/{goal}/progress', [\App\Http\Controllers\FinancialGoalController::class, 'showProgress'])->name('goals.progress');

    // Analytics routes
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/expenses', [AnalyticsController::class, 'expenses'])->name('analytics.expenses');
    Route::get('/analytics/income', [AnalyticsController::class, 'income'])->name('analytics.income');
    Route::get('/analytics/comparison', [AnalyticsController::class, 'comparison'])->name('analytics.comparison');

    // Bank Accounts (New)
    Route::resource('bank-accounts', BankAccountController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show']);

    // Chart of Accounts (New)
    Route::resource('chart-of-accounts', ChartOfAccountController::class);

});

require __DIR__.'/auth.php';
