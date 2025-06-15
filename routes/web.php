<?php

// use App\Http\Controllers\BrandController;
// use App\Http\Controllers\CategoryController;
// use App\Http\Controllers\CustomerController;
// use App\Http\Controllers\DasboardController;
// use App\Http\Controllers\OrderController;
// use App\Http\Controllers\ProductController;
// use App\Http\Controllers\ProfileController;
// use App\Http\Controllers\SettingController;
// use App\Http\Controllers\SuscriptionController;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Route;

// Route::middleware(['auth', 'verified'])->group(function () {
//     // Dashboard
//     Route::get('/', [DasboardController::class, 'index'])->name('dashboard');

//     // Perfil
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

//     // Ventas
//     Route::get('/ventas', [OrderController::class, 'index'])->name('order.home');
//     Route::get('/ventas/{folio}', [OrderController::class, 'show'])->name('order.show');

//     // Marcas
//     Route::get('/marcas', [BrandController::class, 'index'])->name('brand.home');
//     Route::post('/marcas/store', [BrandController::class, 'store'])->name('brand.store');
//     Route::post('/marcas/{id}/update', [BrandController::class, 'store'])->name('brand.update');

//     // Categorias 
//     Route::get('/categorias', [CategoryController::class, 'index'])->name('category.home');

//     // Productos
//     Route::get('/productos', [ProductController::class, 'index'])->name('product.home');
//     Route::get('/productos/agregar', [ProductController::class, 'create'])->name('product.create');
//     Route::get('/productos/{slug}', [ProductController::class, 'show'])->name('product.show');
//     Route::get('/productos/{slug}/editar', [ProductController::class, 'edit'])->name('product.edit');

//     // Clientes
//     Route::get('/clientes', [CustomerController::class, 'index'])->name('customer.home');
//     Route::get('/clientes/id', [CustomerController::class, 'index'])->name('customer.show');

//     // Suscripciones
//     Route::get('/ajustes-suscripciones', [SuscriptionController::class, 'index'])->name('settings.suscriptions');
//     Route::get('/ajustes', [SettingController::class, 'index'])->name('settings.global');

// });

// require __DIR__.'/auth.php';

// Auth::routes();