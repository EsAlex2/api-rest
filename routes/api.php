<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryMovementController;

Route::apiResource('statuses', StatusController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('suppliers', SupplierController::class);

Route::get('products/low-stock', [ProductController::class, 'lowStock']);
Route::apiResource('products', ProductController::class);

Route::get('products/{product}/movements', [InventoryMovementController::class, 'productMovements']);
Route::apiResource('movements', InventoryMovementController::class)->only(['index', 'store', 'show']);
