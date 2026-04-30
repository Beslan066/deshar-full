<?php

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
        Route::delete('/{ciry}', [\App\Http\Controllers\Admin\Cities\IndexController::class, 'destroy'])->name('admin.cities.delete');
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
});

require __DIR__.'/auth.php';
