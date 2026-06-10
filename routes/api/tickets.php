<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::get('/tickets/{id}/history', [TicketController::class, 'history']);
    Route::post('/tickets/upload', [TicketController::class, 'upload']);
    Route::get('/tickets/{id}/proof', [TicketController::class, 'downloadProof']);
    Route::get('/tickets/{id}/physical-photo', [TicketController::class, 'downloadPhysicalPhoto']);
});
