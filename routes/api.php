<?php
// routes/api.php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Публичные маршруты с ограничением по частоте
Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:auth');

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:auth');

Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:auth');

Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:auth');

// Защищенные маршруты
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [AuthController::class, 'updatePassword']);
    Route::get('/auth/stats', [AuthController::class, 'stats']);
    Route::delete('/auth/account', [AuthController::class, 'deleteAccount']);

    // Здесь защищенные эндпоинты
});
