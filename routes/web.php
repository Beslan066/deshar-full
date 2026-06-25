<?php


use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => 'auth'], function () {
    Route::get('/', [\App\Http\Controllers\Admin\IndexController::class, 'index'])->name('admin.index');

    // Роуты пользователей
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // AJAX endpoints для динамических выпадающих списков
    Route::get('/users/regions/{countryId}', [UserController::class, 'getRegions'])->name('admin.users.regions');
    Route::get('/users/districts/{regionId}', [UserController::class, 'getDistricts'])->name('admin.users.districts');
    Route::get('/users/cities/{districtId}', [UserController::class, 'getCities'])->name('admin.users.cities');

    Route::group(['namespace' => 'Roles', 'prefix' => 'roles'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\Roles\IndexController::class, 'index'])->name('admin.roles.index');
        Route::get('/create', [\App\Http\Controllers\Admin\Roles\IndexController::class, 'create'])->name('admin.roles.create');
        Route::post('/store', [\App\Http\Controllers\Admin\Roles\IndexController::class, 'store'])->name('admin.roles.store');
        Route::get('/{role}/edit', [\App\Http\Controllers\Admin\Roles\IndexController::class, 'edit'])->name('admin.roles.edit');
        Route::patch('/{role}', [\App\Http\Controllers\Admin\Roles\IndexController::class, 'update'])->name('admin.roles.update');
        Route::delete('/{role}', [\App\Http\Controllers\Admin\Roles\IndexController::class, 'destroy'])->name('admin.roles.delete');
    });

    Route::group(['namespace' => 'Countries', 'prefix' => 'countries'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\Countries\IndexController::class, 'index'])->name('admin.countries.index');
        Route::get('/create', [\App\Http\Controllers\Admin\Countries\IndexController::class, 'create'])->name('admin.countries.create');
        Route::post('/store', [\App\Http\Controllers\Admin\Countries\IndexController::class, 'store'])->name('admin.countries.store');
        Route::get('/{country}/edit', [\App\Http\Controllers\Admin\Countries\IndexController::class, 'edit'])->name('admin.countries.edit');
        Route::patch('/{country}', [\App\Http\Controllers\Admin\Countries\IndexController::class, 'update'])->name('admin.countries.update');
        Route::delete('/{country}', [\App\Http\Controllers\Admin\Countries\IndexController::class, 'destroy'])->name('admin.countries.delete');
    });

    Route::group(['namespace' => 'Cities', 'prefix' => 'cities'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\Cities\IndexController::class, 'index'])->name('admin.cities.index');
        Route::get('/create', [\App\Http\Controllers\Admin\Cities\IndexController::class, 'create'])->name('admin.cities.create');
        Route::post('/store', [\App\Http\Controllers\Admin\Cities\IndexController::class, 'store'])->name('admin.cities.store');
        Route::get('/{city}/edit', [\App\Http\Controllers\Admin\Cities\IndexController::class, 'edit'])->name('admin.cities.edit');
        Route::patch('/{city}', [\App\Http\Controllers\Admin\Cities\IndexController::class, 'update'])->name('admin.cities.update');
        Route::delete('/{city}', [\App\Http\Controllers\Admin\Cities\IndexController::class, 'destroy'])->name('admin.cities.delete');
    });

    Route::group(['namespace' => 'Districts', 'prefix' => 'districts'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\Districts\IndexController::class, 'index'])->name('admin.districts.index');
        Route::get('/create', [\App\Http\Controllers\Admin\Districts\IndexController::class, 'create'])->name('admin.districts.create');
        Route::post('/store', [\App\Http\Controllers\Admin\Districts\IndexController::class, 'store'])->name('admin.districts.store');
        Route::get('/{district}/edit', [\App\Http\Controllers\Admin\Districts\IndexController::class, 'edit'])->name('admin.districts.edit');
        Route::patch('/{district}', [\App\Http\Controllers\Admin\Districts\IndexController::class, 'update'])->name('admin.districts.update');
        Route::delete('/{district}', [\App\Http\Controllers\Admin\Districts\IndexController::class, 'destroy'])->name('admin.districts.delete');
    });

    Route::group(['namespace' => 'Localities', 'prefix' => 'localities'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\Localities\IndexController::class, 'index'])->name('admin.localities.index');
        Route::get('/create', [\App\Http\Controllers\Admin\Localities\IndexController::class, 'create'])->name('admin.localities.create');
        Route::post('/store', [\App\Http\Controllers\Admin\Localities\IndexController::class, 'store'])->name('admin.localities.store');
        Route::get('/{locality}/edit', [\App\Http\Controllers\Admin\Localities\IndexController::class, 'edit'])->name('admin.localities.edit');
        Route::patch('/{locality}', [\App\Http\Controllers\Admin\Localities\IndexController::class, 'update'])->name('admin.localities.update');
        Route::delete('/{locality}', [\App\Http\Controllers\Admin\Localities\IndexController::class, 'destroy'])->name('admin.localities.delete');
    });

    Route::group(['namespace' => 'Schools', 'prefix' => 'schools'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\Schools\IndexController::class, 'index'])->name('admin.schools.index');
        Route::get('/create', [\App\Http\Controllers\Admin\Schools\IndexController::class, 'create'])->name('admin.schools.create');
        Route::post('/store', [\App\Http\Controllers\Admin\Schools\IndexController::class, 'store'])->name('admin.schools.store');
        Route::get('/{school}/edit', [\App\Http\Controllers\Admin\Schools\IndexController::class, 'edit'])->name('admin.schools.edit');
        Route::patch('/{school}', [\App\Http\Controllers\Admin\Schools\IndexController::class, 'update'])->name('admin.schools.update');
        Route::delete('/{school}', [\App\Http\Controllers\Admin\Schools\IndexController::class, 'destroy'])->name('admin.schools.delete');
    });

    Route::group(['namespace' => 'SchoolClasses', 'prefix' => 'school-classes'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\SchoolClasses\IndexController::class, 'index'])->name('admin.schoolClasses.index');
        Route::get('/create', [\App\Http\Controllers\Admin\SchoolClasses\IndexController::class, 'create'])->name('admin.schoolClasses.create');
        Route::post('/store', [\App\Http\Controllers\Admin\SchoolClasses\IndexController::class, 'store'])->name('admin.schoolClasses.store');
        Route::get('/{schoolClass}/edit', [\App\Http\Controllers\Admin\SchoolClasses\IndexController::class, 'edit'])->name('admin.schoolClasses.edit');
        Route::patch('/{schoolClass}', [\App\Http\Controllers\Admin\SchoolClasses\IndexController::class, 'update'])->name('admin.schoolClasses.update');
        Route::delete('/{schoolClass}', [\App\Http\Controllers\Admin\SchoolClasses\IndexController::class, 'destroy'])->name('admin.schoolClasses.delete');
    });

    Route::group(['namespace' => 'EducationModules', 'prefix' => 'education-modules'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\EducationModules\IndexController::class, 'index'])->name('admin.educationModules.index');
        Route::get('/create', [\App\Http\Controllers\Admin\EducationModules\IndexController::class, 'create'])->name('admin.educationModules.create');
        Route::post('/store', [\App\Http\Controllers\Admin\EducationModules\IndexController::class, 'store'])->name('admin.educationModules.store');
        Route::get('/{schoolClass}/edit', [\App\Http\Controllers\Admin\EducationModules\IndexController::class, 'edit'])->name('admin.educationModules.edit');
        Route::patch('/{schoolClass}', [\App\Http\Controllers\Admin\EducationModules\IndexController::class, 'update'])->name('admin.educationModules.update');
        Route::delete('/{schoolClass}', [\App\Http\Controllers\Admin\EducationModules\IndexController::class, 'destroy'])->name('admin.educationModules.delete');
    });

    Route::group(['namespace' => 'EducationModulesPieces', 'prefix' => 'education-modules-pieces'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\EducationModulePieces\IndexController::class, 'index'])->name('admin.educationModulesPieces.index');
        Route::get('/create', [\App\Http\Controllers\Admin\EducationModulePieces\IndexController::class, 'create'])->name('admin.educationModulesPieces.create');
        Route::post('/store', [\App\Http\Controllers\Admin\EducationModulePieces\IndexController::class, 'store'])->name('admin.educationModulesPieces.store');
        Route::get('/{educationModulePiece}/edit', [\App\Http\Controllers\Admin\EducationModulePieces\IndexController::class, 'edit'])->name('admin.educationModulesPieces.edit');
        Route::patch('/{educationModulePiece}', [\App\Http\Controllers\Admin\EducationModulePieces\IndexController::class, 'update'])->name('admin.educationModulesPieces.update');
        Route::delete('/{educationModulePiece}', [\App\Http\Controllers\Admin\EducationModulePieces\IndexController::class, 'destroy'])->name('admin.educationModulesPieces.delete');
    });

    Route::group(['namespace' => 'Lessons', 'prefix' => 'lessons'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\Lessons\IndexController::class, 'index'])->name('admin.lessons.index');
        Route::get('/create', [\App\Http\Controllers\Admin\Lessons\IndexController::class, 'create'])->name('admin.lessons.create');
        Route::post('/store', [\App\Http\Controllers\Admin\Lessons\IndexController::class, 'store'])->name('admin.lessons.store');
        Route::get('/{lesson}/edit', [\App\Http\Controllers\Admin\Lessons\IndexController::class, 'edit'])->name('admin.lessons.edit');
        Route::patch('/{lesson}', [\App\Http\Controllers\Admin\Lessons\IndexController::class, 'update'])->name('admin.lessons.update');
        Route::delete('/{lesson}', [\App\Http\Controllers\Admin\Lessons\IndexController::class, 'destroy'])->name('admin.lessons.delete');
    });

    Route::group(['namespace' => 'Tasks', 'prefix' => 'tasks'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\Tasks\IndexController::class, 'index'])->name('admin.tasks.index');
        Route::get('/create', [\App\Http\Controllers\Admin\Tasks\IndexController::class, 'create'])->name('admin.tasks.create');
        Route::post('/store', [\App\Http\Controllers\Admin\Tasks\IndexController::class, 'store'])->name('admin.tasks.store');
        Route::get('/{task}/edit', [\App\Http\Controllers\Admin\Tasks\IndexController::class, 'edit'])->name('admin.tasks.edit');
        Route::patch('/{task}', [\App\Http\Controllers\Admin\Tasks\IndexController::class, 'update'])->name('admin.tasks.update');
        Route::delete('/{task}', [\App\Http\Controllers\Admin\Tasks\IndexController::class, 'destroy'])->name('admin.tasks.delete');
    });

    // AJAX для получения дефолтного конфига
    Route::get('tasks/default-config', [\App\Http\Controllers\Admin\Tasks\IndexController::class, 'getDefaultConfig'])
        ->name('admin.tasks.defaultConfig');


    Route::group(['namespace' => 'TaskTypes', 'prefix' => 'task-types'], function () {
        Route::get('/', [\App\Http\Controllers\Admin\TaskTypes\IndexController::class, 'index'])->name('admin.taskTypes.index');
        Route::get('/create', [\App\Http\Controllers\Admin\TaskTypes\IndexController::class, 'create'])->name('admin.taskTypes.create');
        Route::post('/store', [\App\Http\Controllers\Admin\TaskTypes\IndexController::class, 'store'])->name('admin.taskTypes.store');
        Route::get('/{taskType}/edit', [\App\Http\Controllers\Admin\TaskTypes\IndexController::class, 'edit'])->name('admin.taskTypes.edit');
        Route::patch('/{taskType}', [\App\Http\Controllers\Admin\TaskTypes\IndexController::class, 'update'])->name('admin.taskTypes.update');
        Route::delete('/{taskType}', [\App\Http\Controllers\Admin\TaskTypes\IndexController::class, 'destroy'])->name('admin.taskTypes.delete');
    });

});

require __DIR__.'/auth.php';
