<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/tickets/{id}', [TicketController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tickets/upload', [TicketController::class, 'upload']);
});
