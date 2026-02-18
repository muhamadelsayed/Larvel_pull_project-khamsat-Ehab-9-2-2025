<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TruckController;
use App\Http\Controllers\Api\UserTrucksController;
use App\Http\Controllers\Api\MyTrucksController; // <-- إضافة جديدة
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\SubCategoryTrucksController;
use App\Http\Controllers\Api\TruckStatusController;
use App\Http\Controllers\Api\FcmController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    // ----------------------------------------------------------------------
    Route::post('/update-location', [AuthController::class, 'updateLocation']);
    Route::post('/profile/update', [AuthController::class, 'update_profile']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // المسار الجديد لعرض شاحنات مستخدم معين
    Route::get('/users/{user}/trucks', [UserTrucksController::class, 'index']);
    // ----------------------------------------------------------------------
    // هذا المسار مخصص لصاحب الشاحنة ليرى شاحناته فقط
    Route::get('/my-trucks', [MyTrucksController::class, 'index']);
    // إلغاء تفعيل شاحنة
    Route::post('/my-trucks/{truck}/deactivate', [TruckStatusController::class, 'deactivate']);

    // طلب إعادة تفعيل شاحنة
    Route::post('/my-trucks/{truck}/request-activation', [TruckStatusController::class, 'requestActivation']);
    // ----------------------------------------------------------------------
    Route::get('/trucks', [TruckController::class, 'index']);
    Route::post('/trucks', [TruckController::class, 'store']);
    Route::post('/trucks/{truck}', [TruckController::class, 'update']); // استخدام POST للتوافق مع رفع الملفات
    Route::delete('/trucks/{truck}', [TruckController::class, 'destroy']); // <-- إضافة جديدة
    // ----------------------------------------------------------------------
    // اضافة حجز جديد
    Route::post('/bookings', [BookingController::class, 'store']);
    // قائمة حجوزاتي (كعميل أو كمالك شاحنة)
    Route::get('/my-bookings', [BookingController::class, 'index']);
    // الموافقة على حجز (لصاحب الشاحنة)
    Route::post('/my-bookings/{booking}/approve', [BookingController::class, 'approve']);
    // رفض حجز (لصاحب الشاحنة)
    Route::post('/my-bookings/{booking}/reject', [BookingController::class, 'reject']);
    // cancel حجز (كعميل) و الحالة ليست confirmed , completed , rejected , cancelled 
    Route::post('/my-bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    // ----------------------------------------------------------------------
    Route::get('/sub-categories/{subCategory}/trucks', [SubCategoryTrucksController::class, 'index']);

    // -->> مسارات إدارة FCM Tokens <<--
    // إضافة/تحديث التوكين عند تسجيل الدخول أو فتح التطبيق
    Route::post('/fcm-token', [FcmController::class, 'updateToken']);
    // حذف التوكين عند تسجيل الخروج
    Route::delete('/fcm-token', [FcmController::class, 'deleteToken']);

    // -->> مسار جلب الإشعارات <<--
    Route::get('/notifications', [NotificationController::class, 'index']);
    // payment route
    Route::post('/payment/tap/initiate', [PaymentController::class, 'createTapCharge']);
    // مسار للتحقق من حالة الدفع لحجز معين (يمكن للعميل أو المالك أو الأدمن استخدامه)
    Route::get('/bookings/{booking}/verify-payment', [PaymentController::class, 'verifyBookingPayment'])->middleware('auth:sanctum');

});
Route::get('/trucks/{truck}', [TruckController::class, 'show']);
Route::get('/trucks/{truck}/calendar', [CalendarController::class, 'getBookedDates']);

// مسارات للعودة والويب هوك (يجب أن تكون عامة)
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
