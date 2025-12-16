<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Auth endpoints
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    
    /*
    |--------------------------------------------------------------------------
    | POS Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('pos')->name('pos.')->group(function () {
        // Tables & Areas
        Route::get('/tables', [PosController::class, 'tables']);
        Route::get('/table-areas', [PosController::class, 'tableAreas']);
        
        // Products & Categories
        Route::get('/categories', [PosController::class, 'categories']);
        Route::get('/products', [PosController::class, 'products']);
        
        // Journals (for order auto-generation)
        Route::get('/journals', [PosController::class, 'journals']);
        
        // Orders
        Route::post('/orders', [PosController::class, 'createOrder']);
        Route::get('/orders/{order}', [PosController::class, 'showOrder']);
        Route::post('/orders/{order}/items', [PosController::class, 'addItems']);
        Route::delete('/orders/{order}/items/{item}', [PosController::class, 'removeItem']);
        
        // Payments
        Route::get('/payment-methods', [PosController::class, 'paymentMethods']);
        Route::post('/orders/{order}/payment', [PosController::class, 'processPayment']);
        Route::patch('/orders/{order}/close', [PosController::class, 'closeOrder']);
        Route::get('/pending-payments', [PosController::class, 'getPendingPayments']);
    });
    
    /*
    |--------------------------------------------------------------------------
    | User Routes (Employees)
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/waiters', [UserController::class, 'waiters']);
        Route::get('/cashiers', [UserController::class, 'cashiers']);
        Route::get('/{user}', [UserController::class, 'show']);
    });
    
    /*
    |--------------------------------------------------------------------------
    | Kitchen Routes (KDS)
    |--------------------------------------------------------------------------
    */
    Route::prefix('kitchen')->name('kitchen.')->group(function () {
        // Tickets
        Route::get('/tickets', [KitchenController::class, 'tickets']);
        Route::get('/tickets/station/{station}', [KitchenController::class, 'stationTickets']);
        Route::patch('/tickets/{ticket}/start', [KitchenController::class, 'startTicket']);
        Route::patch('/tickets/{ticket}/complete', [KitchenController::class, 'completeTicket']);
        Route::patch('/tickets/{ticket}/deliver', [KitchenController::class, 'deliverTicket']);
        
        // Stations
        Route::get('/stations', [KitchenController::class, 'stations']);
    });
});

/*
|--------------------------------------------------------------------------
| Other API Routes
|--------------------------------------------------------------------------
*/
Route::name('api.')->group(function () {
    Route::apiResource('product-categories', ProductCategoryController::class);
});
