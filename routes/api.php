<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/dashboard.php';
    require __DIR__.'/api/tickets.php';
    require __DIR__.'/api/marketplace.php';
    require __DIR__.'/api/admin.php';
    require __DIR__.'/api/transactions.php';
});
