<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Marketplace\ListingController;

Route::prefix('marketplace')->group(function () {

    Route::get('/listings', [ListingController::class, 'index']);
    Route::get('/listings/{listing}', [ListingController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/listings', [ListingController::class, 'store']);

        Route::put('/listings/{listing}', [ListingController::class, 'update']);

        Route::delete('/listings/{listing}', [ListingController::class, 'destroy']);

    });

});