<?php

use App\Http\Controllers\ProfileAuthorController;
use App\Http\Controllers\ProfileProgramController;
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

Route::prefix('profile-program')->group(function () {
    Route::get('/paginate', [ProfileProgramController::class, 'getPaginate']);
    Route::get('/{id}', [ProfileProgramController::class, 'show']);
    Route::get('/', [ProfileProgramController::class, 'index']);
    Route::post('/', [ProfileProgramController::class, 'store']);
    Route::put('/{id}', [ProfileProgramController::class, 'update']);
    Route::delete('/{id}', [ProfileProgramController::class, 'destroy']);
    // Route::post('/sync-from-sinta', [ProfileProgramController::class, 'syncFromSinta']);
});