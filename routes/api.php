<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NewsLetterController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DistribuitorController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SuscriptionController;

// Autenticacion

Route::post('/login', [AuthController::class, 'signIn' ]);

Route::post('/account', [AuthController::class, 'signUp']);

// Rutas protegidas

Route::middleware('auth:sanctum')->group(function () {

    // Rutas de cuenta

    Route::delete('/logout', [AuthController::class, 'signOut']);

    Route::post('/verify-auth', [AuthController::class, 'verifyAuth']);

    Route::get('/profile', [AccountController::class, 'show']);

    // Direcciones

    Route::get('/addresses', [AddressController::class, 'index']);

    Route::post('/addresses', [AddressController::class, 'store']);

    Route::put('/addresses/{id}', [AddressController::class, 'update']);

    Route::delete('/addresses/{id}', [AddressController::class, 'delete']);

    // Rutas de compras

    Route::get('/orders', [OrderController::class, 'index']);

    Route::post('/orders', [OrderController::class, 'store']);

});

// Stripe

Route::prefix('/order')->group(function () {

    Route::post('/{folio}/succeded', [OrderController::class, 'succeded']);
    
});

// Catalogos

Route::get('/brands', [BrandController::class, 'index']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/subscriptions', [SuscriptionController::class, 'index']);

Route::get('/products', [ProductController::class, 'index']);

Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::get('/{category_id}/products', [ProductController::class, 'byCategory']);

Route::get('/distribuitors', [DistribuitorController::class, 'index']);
