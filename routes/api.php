<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolesController;

// Rutas Públicas
Route::post('login', [AuthController::class, 'login']);

// Rutas Protegidas por Autenticación (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación de sesión activa
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Rutas de Administración de Usuarios
    Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:users.view');
    Route::post('users', [UserController::class, 'store'])->middleware('permission:users.manage');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.manage');

    // Rutas de Roles y Permisos (Administración)
    Route::get('permissions', [RolesController::class, 'listPermissions'])->middleware('permission:users.manage');
    Route::apiResource('roles', RolesController::class)->middleware('permission:users.manage');

    // Rutas del Sistema de Gestión de Inventario
    
    // Estados
    Route::get('statuses', [StatusController::class, 'index'])->middleware('permission:products.view');
    Route::get('statuses/{status}', [StatusController::class, 'show'])->middleware('permission:products.view');
    Route::post('statuses', [StatusController::class, 'store'])->middleware('permission:statuses.manage');
    Route::put('statuses/{status}', [StatusController::class, 'update'])->middleware('permission:statuses.manage');
    Route::delete('statuses/{status}', [StatusController::class, 'destroy'])->middleware('permission:statuses.manage');

    // Categorías
    Route::get('categories', [CategoryController::class, 'index'])->middleware('permission:products.view');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->middleware('permission:products.view');
    Route::post('categories', [CategoryController::class, 'store'])->middleware('permission:categories.manage');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.manage');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.manage');

    // Proveedores
    Route::get('suppliers', [SupplierController::class, 'index'])->middleware('permission:products.view');
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->middleware('permission:products.view');
    Route::post('suppliers', [SupplierController::class, 'store'])->middleware('permission:suppliers.manage');
    Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('permission:suppliers.manage');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.manage');

    // Productos
    Route::get('products/low-stock', [ProductController::class, 'lowStock'])->middleware('permission:products.view');
    Route::get('products', [ProductController::class, 'index'])->middleware('permission:products.view');
    Route::get('products/{product}', [ProductController::class, 'show'])->middleware('permission:products.view');
    Route::post('products', [ProductController::class, 'store'])->middleware('permission:products.manage');
    Route::put('products/{product}', [ProductController::class, 'update'])->middleware('permission:products.manage');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.manage');

    // Movimientos de Inventario
    Route::get('movements', [InventoryMovementController::class, 'index'])->middleware('permission:movements.view');
    Route::get('movements/{movement}', [InventoryMovementController::class, 'show'])->middleware('permission:movements.view');
    Route::post('movements', [InventoryMovementController::class, 'store'])->middleware('permission:movements.manage');
    Route::get('products/{product}/movements', [InventoryMovementController::class, 'productMovements'])->middleware('permission:movements.view');
});
