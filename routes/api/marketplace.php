<?php

use App\Http\Controllers\Api\ResaleListingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/marketplace/listings', [ResaleListingController::class, 'store']);
});
