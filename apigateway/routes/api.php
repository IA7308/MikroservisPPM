<?php

use App\Http\Controllers\GatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('doc-book-author')->group(function () {
    Route::get('/', [GatewayController::class, 'index']);
    Route::get('/{id}', [GatewayController::class, 'findByID']);
    Route::post('/', [GatewayController::class, 'storeBook']);
});
