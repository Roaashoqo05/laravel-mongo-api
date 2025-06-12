<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarPartController;
use App\Http\Controllers\InvoiceController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Test Routes
Route::get('/register-test', function () {
    return response()->json(['message' => 'Route is working']);
});

Route::get('/test-mongodb', function() {
    try {
        CarPart::create(['name' => 'Test Part', 'price' => 10.99]);
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Car Parts Routes
Route::prefix('car-parts')->group(function () { 
    Route::get('/', [CarPartController::class, 'index']);
    Route::get('/search', [CarPartController::class, 'search']);
    Route::post('/', [CarPartController::class, 'store']);
});

// Invoices Routes
Route::prefix('invoices')->group(function () {
    Route::post('/', [InvoiceController::class, 'create']);
    Route::get('/{id}', [InvoiceController::class, 'show']);
    Route::get('/', [InvoiceController::class, 'index']);
});
