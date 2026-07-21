<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Rutas Públicas
Route::post('login', [AuthController::class, 'login']);

// Rutas Protegidas por Autenticación (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación de sesión activa
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Rutas de Administración de Usuarios (Permisos controlados en el Controlador)
    Route::apiResource('users', UserController::class)->except(['update']);

    // Rutas del Sistema de Gestión de Inventario
    Route::apiResource('statuses', StatusController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers', SupplierController::class);

    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    Route::apiResource('products', ProductController::class);

    Route::get('products/{product}/movements', [InventoryMovementController::class, 'productMovements']);
    Route::apiResource('movements', InventoryMovementController::class)->only(['index', 'store', 'show']);
});
