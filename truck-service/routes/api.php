<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/update-location', [AuthController::class, 'updateLocation']);
    // يمكنك إضافة أي مسارات مستقبلية تتطلب مصادقة هنا
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});