<?php

use App\Http\Controllers\DocGarudaAuthorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('doc-garuda-author')->group(function () {
    Route::get('/', [DocGarudaAuthorController::class, 'index']);
    Route::get('/{id}', [DocGarudaAuthorController::class, 'show']);
    Route::get('/author/{authorId}', [DocGarudaAuthorController::class, 'showByAuthorId']);
    Route::post('/', [DocGarudaAuthorController::class, 'store']);
    Route::put('/{id}', [DocGarudaAuthorController::class, 'update']);
    Route::delete('/{id}', [DocGarudaAuthorController::class, 'destroy']);
    // Route::post('/sync-from-sinta', [DocGarudaAuthorController::class, 'syncFromSinta'])->middleware(['auth:sanctum', 'auth.check:Administrator,Staff']);
});