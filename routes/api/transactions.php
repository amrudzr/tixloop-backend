<?php

use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions/{id}/simulate-payment', [TransactionController::class, 'simulatePayment']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
});
