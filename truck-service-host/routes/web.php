<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RegisterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\TruckController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;

use Illuminate\Support\Facades\Artisan; // <-- استيراد مهم
use Illuminate\Support\Facades\File;    // <-- استيراد File

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| كل مسارات لوحة التحكم محمية بـ 'auth:web' و 'can:view users'
|
*/

// المسار الرئيسي يوجه لصفحة تسجيل الدخول
Route::get('/', function () {
    return redirect()->route('admin.login');
});


// =========================================================================
// == مسارات الضيوف والمصادقة (Authentication)
// =========================================================================

Route::get('admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [LoginController::class, 'login']);
Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
Route::get('access-denied', function () {
    return view('admin.access-denied');
})->name('access.denied');
Route::get('admin/register', [RegisterController::class, 'showRegistrationForm'])->name('admin.register');


// =========================================================================
// == مجموعة مسارات لوحة التحكم المحمية
// =========================================================================
Route::middleware(['auth:web', 'can:view users'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // --- إدارة المستخدمين ---
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role.update')->middleware('can:promote users');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy')
        ->middleware('can:delete users');
    // إرسال إشعار لمستخدم واحد
    Route::post('/users/{user}/notify', [NotificationController::class, 'sendToUser'])->name('users.notify.send')->middleware('can:manage trucks');
    // حظر مستخدم
    Route::patch('/users/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('users.toggle-block')->middleware('can:promote users');

    // --- إدارة التصنيفات ---
    Route::resource('categories', CategoryController::class)->middleware('can:manage categories');
    Route::prefix('categories/{category}/sub-categories')
        ->name('sub_categories.')
        ->middleware('can:manage categories')
        ->controller(SubCategoryController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{subCategory}/edit', 'edit')->name('edit');
            Route::patch('/{subCategory}', 'update')->name('update');
            Route::delete('/{subCategory}', 'destroy')->name('destroy');
    });
    
    // --- إدارة الشاحنات ---
    Route::get('/trucks', [TruckController::class, 'index'])->name('trucks.index')->middleware('can:manage trucks');
    Route::get('/trucks/{truck}', [TruckController::class, 'show'])->name('trucks.show')->middleware('can:manage trucks');
    Route::patch('/trucks/{truck}/status', [TruckController::class, 'updateStatus'])->name('trucks.status.update')->middleware('can:manage trucks');

    // --- إدارة الحجوزات ---
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index')->middleware('can:manage trucks');
    Route::get('/bookings/{booking}/status', [BookingController::class, 'showStatusPage'])->name('bookings.status')->middleware('can:manage trucks');
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status.update')->middleware('can:manage trucks');
    
    // --- إدارة الإشعارات ---
    Route::get('/notifications/broadcast', [NotificationController::class, 'showBroadcastForm'])->name('notifications.broadcast.form')->middleware('can:manage trucks');
    Route::post('/notifications/broadcast', [NotificationController::class, 'sendBroadcast'])->name('notifications.broadcast.send')->middleware('can:manage trucks');
    
    // الاعدادات
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});

Route::get('/system/run-booking-completion', function () {
    try {
        // تنفيذ الأمر البرمجي
        Artisan::call('bookings:complete');
        
        $output = Artisan::output();
        
        return response()->json([
            'status' => 'success',
            'message' => 'تم تشغيل مهمة إكمال الحجوزات بنجاح.',
            'details' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// 2. فحص حالة الجدولة (Schedule List) - لرؤية متى ستعمل المهمة القادمة
Route::get('/system/schedule-list', function () {
    try {
        Artisan::call('schedule:list');
        return "<pre>" . Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});


// =========================================================================
// == مسارات التنفيذ المؤقتة (***مهمة للنشر بدون SSH، يجب حذفها بعد الاستخدام***)
// =========================================================================

// مسار لتنفيذ الترحيلات الجديدة (إنشاء الجداول الجديدة فقط)
// Route::get('/system/migrate', function () {
//     try {
//         Artisan::call('migrate', ['--force' => true]);
//         return response()->json(['message' => 'Migrations Executed Successfully!'], 200);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// });

// // مسار لتنفيذ الـ Seeders (لإنشاء الأدوار والصلاحيات)
// Route::get('/system/seed-roles', function () {
//     try {
//         Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--force' => true]);
//         return response()->json(['message' => 'Roles and Permissions Seeded Successfully!'], 200);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// });

// // مسار لمسح الكاش العام
// Route::get('/system/clear-cache', function () {
//    try {
//         Artisan::call('optimize:clear'); 
//         return response()->json(['message' => 'Cache Cleared Successfully (View, Route, Config, Compiled)!'], 200);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// });

// https://bull-station.com/system/clear-cache
//  https://bull-station.com/system/migrate
//  https://bull-station.com/system/seed-roles