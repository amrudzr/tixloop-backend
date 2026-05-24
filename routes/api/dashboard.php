<?php

use App\Http\Controllers\Api\Dashboard\WasteDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {

    Route::get('/waste', [WasteDashboardController::class, 'index']);

});
