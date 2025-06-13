<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\DasboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/', [DasboardController::class, 'index'])->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ventas
    Route::get('/ventas', [OrderController::class, 'index'])->name('order.home');
    Route::get('/ventas/{folio}', [OrderController::class, 'show'])->name('order.show');

    // Marcas
    Route::get('/marcas', [BrandController::class, 'index'])->name('brand.home');

});

require __DIR__.'/auth.php';

Auth::routes();