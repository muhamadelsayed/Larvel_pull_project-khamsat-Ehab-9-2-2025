<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TruckController;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/update-location', [AuthController::class, 'updateLocation']);
    // يمكنك إضافة أي مسارات مستقبلية تتطلب مصادقة هنا
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/trucks', [TruckController::class, 'store']);
    Route::post('/trucks/{truck}', [TruckController::class, 'update']); // استخدام POST للتوافق مع رفع الملفات
    Route::delete('/trucks/{truck}', [TruckController::class, 'destroy']); // <-- إضافة جديدة

});
Route::get('/trucks/{truck}', [TruckController::class, 'show']);