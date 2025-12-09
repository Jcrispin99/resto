<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| POS Routes (Vue SPA)
|--------------------------------------------------------------------------
*/
Route::get('/pos/{any?}', function () {
    return view('pos');
})->where('any', '.*')->name('pos');


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
    Route::resource('purchase-orders', \App\Http\Controllers\PurchaseOrderController::class);
    Route::resource('sale-orders', \App\Http\Controllers\SaleOrderController::class);
    Route::resource('stock-transfers', \App\Http\Controllers\StockTransferController::class);
    Route::resource('product-categories', \App\Http\Controllers\ProductCategoryController::class);
    Route::resource('product-attributes', \App\Http\Controllers\ProductAttributeController::class);
    Route::resource('product-templates', \App\Http\Controllers\ProductTemplateController::class);
    
    // Recipes & Combos
    Route::resource('recipes', \App\Http\Controllers\RecipeController::class);
    Route::get('/recipes/calculate-cost/{productTemplate}', [\App\Http\Controllers\RecipeController::class, 'calculateCost']);
    Route::resource('combos', \App\Http\Controllers\ComboController::class);

    // POS Management
    Route::resource('payment-methods', \App\Http\Controllers\PaymentMethodController::class);
    Route::resource('pos-terminals', \App\Http\Controllers\PosTerminalController::class);
    Route::resource('cash-registers', \App\Http\Controllers\CashRegisterController::class)->except(['edit', 'update']);
    Route::post('/cash-registers/{cashRegister}/close', [\App\Http\Controllers\CashRegisterController::class, 'close'])->name('cash-registers.close');
    Route::post('/cash-registers/{cashRegister}/add-movement', [\App\Http\Controllers\CashRegisterController::class, 'addMovement'])->name('cash-registers.add-movement');

    // Tables & Reservations
    Route::resource('table-areas', \App\Http\Controllers\TableAreaController::class);
    Route::resource('tables', \App\Http\Controllers\TableController::class);
    Route::post('/tables/{table}/update-status', [\App\Http\Controllers\TableController::class, 'updateStatus'])->name('tables.update-status');
    Route::resource('reservations', \App\Http\Controllers\ReservationController::class);
    Route::post('/reservations/{reservation}/confirm', [\App\Http\Controllers\ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation}/seat', [\App\Http\Controllers\ReservationController::class, 'seat'])->name('reservations.seat');
    Route::post('/reservations/{reservation}/cancel', [\App\Http\Controllers\ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservations/{reservation}/no-show', [\App\Http\Controllers\ReservationController::class, 'noShow'])->name('reservations.no-show');
    
    // API routes for autocomplete/search
    Route::get('/api/products/search', [\App\Http\Controllers\ProductSearchController::class, 'search']);
    Route::get('/api/partners/search', [\App\Http\Controllers\PartnerController::class, 'search']);
});

require __DIR__.'/settings.php';
