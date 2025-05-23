<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarPartController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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
});

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/car-parts', [CarPartController::class, 'store']);
});