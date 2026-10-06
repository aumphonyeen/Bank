<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BankingDataController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        $html = file_get_contents(public_path('index.html'));

        abort_if($html === false, 500, 'ไม่สามารถอ่านหน้า Dashboard ได้');

        return response(str_replace('__BANKLITE_CSRF_TOKEN__', csrf_token(), $html))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    })->name('dashboard');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/api/dashboard', DashboardController::class);
    Route::post('/api/customers', [BankingDataController::class, 'storeCustomer']);
    Route::put('/api/customers/{id}', [BankingDataController::class, 'updateCustomer']);
    Route::delete('/api/customers/{id}', [BankingDataController::class, 'deleteCustomer']);
    Route::post('/api/accounts', [BankingDataController::class, 'storeAccount']);
    Route::put('/api/accounts/{id}', [BankingDataController::class, 'updateAccount']);
    Route::delete('/api/accounts/{id}', [BankingDataController::class, 'deleteAccount']);
    Route::post('/api/transactions', [BankingDataController::class, 'storeTransaction']);
    Route::put('/api/transactions/{id}', [BankingDataController::class, 'updateTransaction']);
    Route::delete('/api/transactions/{id}', [BankingDataController::class, 'deleteTransaction']);
});
