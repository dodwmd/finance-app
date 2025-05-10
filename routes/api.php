<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BudgetController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ChartOfAccountController;
use App\Http\Controllers\Api\V1\FinancialGoalController;
use App\Http\Controllers\Api\V1\RecurringTransactionController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\RegisterUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API V1 Routes
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public routes with rate limiting for guests
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/register', RegisterUserController::class)->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    // Resource routes
    Route::apiResource('users', UserController::class);

    // Protected routes with rate limiting for authenticated users
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        // Auth routes
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('logout.all');

        // Transaction routes
        Route::apiResource('transactions', TransactionController::class);
        Route::get('transactions/summary', [TransactionController::class, 'summary'])->name('transactions.summary');

        // Category routes
        Route::apiResource('categories', CategoryController::class);

        // Budget routes
        Route::apiResource('budgets', BudgetController::class);
        Route::get('budgets/progress', [BudgetController::class, 'progress'])->name('budgets.progress');

        // Financial Goal routes
        Route::apiResource('financial-goals', FinancialGoalController::class);
        Route::get('financial-goals/progress', [FinancialGoalController::class, 'progress'])->name('financial-goals.progress');

        // Recurring Transaction routes
        Route::apiResource('recurring-transactions', RecurringTransactionController::class);
        Route::get('recurring-transactions/due', [RecurringTransactionController::class, 'due'])->name('recurring-transactions.due');

        // Chart of Account routes
        Route::apiResource('chart-of-accounts', ChartOfAccountController::class);
    });
});
