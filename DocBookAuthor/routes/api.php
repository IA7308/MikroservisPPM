<?php

use App\Http\Controllers\docBookAuthorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('doc-book-author')->group(function () {
    Route::get('/', [docBookAuthorController::class, 'index']);
    Route::get('/AC', [docBookAuthorController::class, 'indexAC']);
    Route::get('/{id}', [docBookAuthorController::class, 'show']);
    Route::get('/author/{authorId}', [docBookAuthorController::class, 'showByAuthorId']);
    Route::post('/', [docBookAuthorController::class, 'store']);
    Route::post('/AC', [docBookAuthorController::class, 'storeAC']);
    Route::put('/{id}', [docBookAuthorController::class, 'update']);
    Route::put('/{id}/AC', [docBookAuthorController::class, 'updateAC']);
    Route::delete('/{id}', [docBookAuthorController::class, 'destroy']);
    Route::delete('/{id}/AC', [docBookAuthorController::class, 'destroyAC']);
    // Route::post('/sync-from-sinta', [docBookAuthorController::class, 'syncFromSinta'])->middleware(['auth:sanctum', 'auth.check:Administrator,Staff']);
});