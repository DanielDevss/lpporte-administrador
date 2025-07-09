<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DistribuitorController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SuscriptionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/brands', [BrandController::class, 'index']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/subscriptions', [SuscriptionController::class, 'index']);

Route::get('/products', [ProductController::class, 'index']);

Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/{category_id}/products', [ProductController::class, 'byCategory']);

Route::get('/distribuitors', [DistribuitorController::class, 'index']);
