<?php

use App\Http\Controllers\Api\ResaleListingController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// Public browsing routes (no authentication required)
Route::get('/marketplace/listings', [ResaleListingController::class, 'index']);
Route::get('/marketplace/listings/{id}', [ResaleListingController::class, 'show']);

// Protected seller routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/marketplace/listings', [ResaleListingController::class, 'store']);
    Route::post('/marketplace/listings/{id}/checkout', [TransactionController::class, 'checkout']);
});
