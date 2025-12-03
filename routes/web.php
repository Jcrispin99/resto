<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('companies', \App\Http\Controllers\CompanyController::class);
    Route::resource('warehouses', \App\Http\Controllers\WarehouseController::class);
    Route::resource('units', \App\Http\Controllers\UnitController::class);
    Route::resource('partners', \App\Http\Controllers\PartnerController::class);
    Route::resource('product-categories', \App\Http\Controllers\ProductCategoryController::class);
    Route::resource('product-attributes', \App\Http\Controllers\ProductAttributeController::class);
    Route::resource('product-templates', \App\Http\Controllers\ProductTemplateController::class);
});

require __DIR__.'/settings.php';
