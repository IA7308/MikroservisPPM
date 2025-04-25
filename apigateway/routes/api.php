<?php

use App\Http\Controllers\GatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('doc-book-author')->group(function () {
    Route::get('/', [GatewayController::class, 'index']);
    Route::get('/{id}', [GatewayController::class, 'findByID']);
    Route::post('/', [GatewayController::class, 'storeBook']);
    Route::put('/{id}', [GatewayController::class, 'updateBook']);
    // Route::put('/{id}/AC', [GatewayController::class, 'updateAC']);
    Route::delete('/{id}', [GatewayController::class, 'deleteBook']);
    // Route::delete('/{id}/AC', [GatewayController::class, 'destroyAC']);
});
