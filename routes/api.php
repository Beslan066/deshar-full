<?php
// routes/api.php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Frontend\EducationDepartment\EducationDepartmentController;
use App\Http\Controllers\Api\Frontend\Ministry\MinistryRepresentativeController;
use App\Http\Controllers\Api\Frontend\SchoolManager\SchoolManagementController;
use App\Http\Controllers\Api\Frontend\Student\EducationModuleController;
use App\Http\Controllers\Api\Frontend\Student\LessonController;
use App\Http\Controllers\Api\Frontend\Student\PieceController;
use App\Http\Controllers\Api\Frontend\Student\TaskController;
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

    Route::group(['namespace' => 'Region', 'prefix' => 'regions'], function () {
        Route::get('/', [App\Http\Controllers\Api\RegionController::class, 'index']);
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


    //  МАРШРУТЫ ДЛЯ ОБРАЗОВАТЕЛЬНЫХ МОДУЛЕЙ
    Route::group(['middleware' => 'auth:sanctum','namespace' => 'Api'], function () {
        Route::group(['prefix' => 'modules'], function () {
            // --- МАРШРУТЫ ДЛЯ МОДУЛЕЙ ---
            Route::get('/', [EducationModuleController::class, 'index']);
            Route::get('/progress', [EducationModuleController::class, 'progress']);
            Route::get('/recommended', [EducationModuleController::class, 'recommended']);
            Route::get('/{module}', [EducationModuleController::class, 'show']);
            Route::get('/{module}/progress', [EducationModuleController::class, 'moduleProgress']);

            // --- МАРШРУТЫ ДЛЯ РАЗДЕЛОВ (PIECES) ---
            Route::get('/{module}/pieces', [PieceController::class, 'index']);
            Route::get('/{module}/pieces/{piece}', [PieceController::class, 'show']);
            Route::get('/{module}/pieces/{piece}/progress', [PieceController::class, 'progress']);

            // --- МАРШРУТЫ ДЛЯ УРОКОВ (LESSONS) ---
            Route::get('/{module}/pieces/{piece}/lessons', [LessonController::class, 'index']);
            Route::get('/{module}/pieces/{piece}/lessons/{lesson}', [LessonController::class, 'show']);
            Route::get('/{module}/pieces/{piece}/lessons/{lesson}/progress', [LessonController::class, 'progress']);

            // --- МАРШРУТЫ ДЛЯ ЗАДАНИЙ (TASKS) ---
            Route::get('/{module}/pieces/{piece}/lessons/{lesson}/tasks', [TaskController::class, 'index']);
            Route::get('/{module}/pieces/{piece}/lessons/{lesson}/tasks/{task}', [TaskController::class, 'show']);
            Route::post('/{module}/pieces/{piece}/lessons/{lesson}/tasks/{task}/complete', [TaskController::class, 'complete']);
        });

        Route::group(['prefix' => 'school'], function () {
            // Получить все классы в школе
            Route::get('/classes', [SchoolManagementController::class, 'getClasses']);

            // Получить учеников класса
            Route::get('/classes/{classId}/students', [SchoolManagementController::class, 'getClassStudents']);

            // Получить учителей школы
            Route::get('/teachers', [SchoolManagementController::class, 'getTeachers']);

            // Получить учителей школы
            Route::get('/teachers', [SchoolManagementController::class, 'getTeachers']);

            // Получить учителей с детальной статистикой
            Route::get('/teachers/stats', [SchoolManagementController::class, 'getTeachersWithStats']);

            // Получить статистику по школе
            Route::get('/statistics', [SchoolManagementController::class, 'getStatistics']);

            // Получить статистику по конкретному классу
            Route::get('/classes/{classId}/statistics', [SchoolManagementController::class, 'getClassStatistics']);

            // Получить прогресс ученика
            Route::get('/students/{userId}/progress', [SchoolManagementController::class, 'getStudentProgress']);

            // Получить всех учеников школы с их прогрессом
            Route::get('/students', [SchoolManagementController::class, 'getAllStudents']);

            // Экспорт данных в CSV
            Route::get('/export', [SchoolManagementController::class, 'exportData']);
        });

        Route::group([ 'prefix' => 'ministry'], function () {
            Route::get('/districts', [MinistryRepresentativeController::class, 'districts']);
            Route::get('/schools', [MinistryRepresentativeController::class, 'schools']);
            Route::get('/schools/{school}/stats', [MinistryRepresentativeController::class, 'schoolStats']);
            Route::get('/districts/{district}/stats', [MinistryRepresentativeController::class, 'districtStats']);
            Route::get('/republic/stats', [MinistryRepresentativeController::class, 'republicStats']);
        });

        // Пр. Управления образования (role_id: 5)
        Route::group(['prefix' => 'education-department'], function () {
            Route::get('/my-district', [EducationDepartmentController::class, 'myDistrict']);
            Route::get('/schools', [EducationDepartmentController::class, 'schools']);
            Route::get('/schools/{school}/stats', [EducationDepartmentController::class, 'schoolStats']);
            Route::get('/district/stats', [EducationDepartmentController::class, 'districtStats']);
            Route::get('/students', [EducationDepartmentController::class, 'students']);
            Route::get('/teachers', [EducationDepartmentController::class, 'teachers']);
        });
    });
});
