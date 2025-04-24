<?php

use App\Http\Controllers\ProfileFacultyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('profile-faculty')->group(function () {
    Route::get('/', [ProfileFacultyController::class, 'index']);
});
