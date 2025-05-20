<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarPartController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// راوتس تسجيل الدخول والتسجيل
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register-test', function () {
    return response()->json(['message' => 'Route is working']);
});

// راوتس قطع السيارات 
// جلب كل القطع
Route::get('/car-parts', [CarPartController::class, 'index']);
// إضافة قطعة جديدة
Route::post('/car-parts', [CarPartController::class, 'store']);

Route::get('/test-mongodb', function() {
    try {
        CarPart::create(['name' => 'Test Part', 'price' => 10.99]);
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
