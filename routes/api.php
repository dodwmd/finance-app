<?php

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
    // Public routes
    Route::post('/register', RegisterUserController::class)->name('register');
    
    // Resource routes
    Route::apiResource('users', UserController::class);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Transaction routes
        Route::apiResource('transactions', TransactionController::class);
        Route::get('transactions/summary', [TransactionController::class, 'summary'])->name('transactions.summary');
    });
});
