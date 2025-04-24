<?php

use App\Http\Controllers\ProfileAuthorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('profile-author')->group(function () {
    Route::get('/', [ProfileAuthorController::class, 'index']);
    // Route::post('/sync-from-sinta', [ProfileAuthorController::class, 'syncFromSinta'])->middleware(['auth:sanctum', 'auth.check:Administrator,Staff']);
    Route::get('/{id}', [ProfileAuthorController::class, 'show']);
    Route::post('/', [ProfileAuthorController::class, 'store']);
    Route::put('/{id}', [ProfileAuthorController::class, 'update']);
    Route::delete('/{id}', [ProfileAuthorController::class, 'destroy']);
});
