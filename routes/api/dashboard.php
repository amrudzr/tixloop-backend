<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Dashboard\WasteDashboardController;

Route::prefix('dashboard')->group(function () {

    Route::get('/waste', [WasteDashboardController::class, 'index']);

});