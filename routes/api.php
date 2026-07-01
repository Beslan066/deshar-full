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

});


// Публичные маршруты для справочников (ДО регистрации)
Route::group(['namespace' => 'Api'], function () {
    Route::group(['namespace' => 'Country', 'prefix' => 'countries'], function () {
        Route::get('/', [App\Http\Controllers\Api\CountryController::class, 'index']);
    });

    Route::group(['namespace' => 'District', 'prefix' => 'districts'], function () {
        Route::get('/', [App\Http\Controllers\Api\DistrictController::class, 'index']);
    });

    Route::group(['namespace' => 'City', 'prefix' => 'cities'], function () {
        Route::get('/', [App\Http\Controllers\Api\CityController::class, 'index']);
    });

    Route::group(['namespace' => 'Locality', 'prefix' => 'localities'], function () {
        Route::get('/', [App\Http\Controllers\Api\LocalityController::class, 'index']);
    });

    Route::group(['namespace' => 'School', 'prefix' => 'schools'], function () {
        Route::get('/', [App\Http\Controllers\Api\SchoolController::class, 'index']);
    });

    Route::group(['namespace' => 'SchoolClass', 'prefix' => 'school-classes'], function () {
        Route::get('/', [App\Http\Controllers\Api\SchoolClassController::class, 'index']);
    });
});



// Получение текущего пользователя (требует авторизацию)
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Защищенные маршруты (только для авторизованных)
Route::group(['middleware' => 'auth:sanctum','namespace' => 'Api'], function () {
    Route::group(['namespace' => 'Role', 'prefix' => 'roles'], function () {
        Route::get('/', [App\Http\Controllers\Api\RoleController::class, 'index']);
    });

});
