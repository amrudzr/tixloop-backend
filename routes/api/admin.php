<?php

use App\Http\Controllers\Api\Admin\AdminResaleListingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    Route::get('/listings', [AdminResaleListingController::class, 'index']);
    Route::get('/listings/{id}', [AdminResaleListingController::class, 'show']);
    Route::post('/listings/{id}/verify', [AdminResaleListingController::class, 'verify']);
    Route::post('/listings/{id}/reject', [AdminResaleListingController::class, 'reject']);

});
