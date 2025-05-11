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
use App\Http\Controllers\StagedTransactionController;
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
    Route::get('/bank-accounts/{bankAccount}/deposits/create', [BankAccountController::class, 'createDeposit'])->name('bank-accounts.deposits.create');
    Route::post('/bank-accounts/{bankAccount}/deposits', [BankAccountController::class, 'storeDeposit'])->name('bank-accounts.deposits.store');
    Route::get('/bank-accounts/{bankAccount}/withdrawals/create', [BankAccountController::class, 'createWithdrawal'])->name('bank-accounts.withdrawals.create');
    Route::post('/bank-accounts/{bankAccount}/withdrawals', [BankAccountController::class, 'storeWithdrawal'])->name('bank-accounts.withdrawals.store');
    Route::get('/bank-accounts/{bankAccount}/import', [BankAccountController::class, 'showImportForm'])->name('bank-accounts.import.form');
    Route::post('/bank-accounts/{bankAccount}/import', [BankAccountController::class, 'storeImport'])->name('bank-accounts.import.store');
    Route::get('/bank-accounts/{bankAccount}/staged-transactions/review', [BankAccountController::class, 'reviewStagedTransactions'])->name('bank-accounts.staged.review');

    // Bank Statement Import Column Mapping
    Route::get('/bank-accounts/{bankAccount}/imports/{import}/mapping', [BankAccountController::class, 'showMappingForm'])->name('bank-accounts.import.mapping.show');
    Route::put('/bank-accounts/{bankAccount}/imports/{import}/mapping', [BankAccountController::class, 'updateMapping'])->name('bank-accounts.import.mapping.update');

    // Staged Transaction Actions
    Route::post('/staged-transactions/{stagedTransaction}/approve', [StagedTransactionController::class, 'approve'])->name('staged-transactions.approve');
    Route::post('/staged-transactions/{stagedTransaction}/update-category', [StagedTransactionController::class, 'updateCategory'])->name('staged-transactions.update-category');
    Route::post('/staged-transactions/{stagedTransaction}/ignore', [StagedTransactionController::class, 'ignore'])->name('staged-transactions.ignore');

    // Chart of Accounts (New)
    Route::resource('chart-of-accounts', ChartOfAccountController::class);

});

require __DIR__.'/auth.php';
