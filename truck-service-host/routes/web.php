<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RegisterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\TruckController;
use App\Http\Controllers\Admin\BookingController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// المسار الرئيسي، يمكن توجيهه لصفحة تسجيل الدخول مباشرة
Route::get('/', function () {
    return redirect()->route('admin.login');
});


// =========================================================================
// == مسارات المصادقة للوحة التحكم (تسجيل الدخول والخروج)
// =========================================================================

// عرض صفحة تسجيل الدخول
Route::get('admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');

// إرسال بيانات تسجيل الدخول
Route::post('admin/login', [LoginController::class, 'login']);

// تسجيل الخروج
Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');


// =========================================================================
// == صفحة "الوصول مرفوض" للمستخدمين العاديين
// =========================================================================

Route::get('access-denied', function () {
    // يمكنك هنا إنشاء واجهة view مخصصة وجذابة كما طلبت
    return view('admin.access-denied');
})->name('access.denied');


// =========================================================================
// == مجموعة مسارات لوحة التحكم المحمية
// =========================================================================
// - prefix('admin'): يجعل كل المسارات تبدأ بـ /admin
// - name('admin.'): يجعل كل أسماء المسارات تبدأ بـ admin.
// - middleware(['auth:web', 'can:view users']): يحمي جميع هذه المسارات
//   1. 'auth:web': يتأكد أن المستخدم مسجل دخوله (عبر الجلسات Sessions).
//   2. 'can:view users': يتأكد أن المستخدم لديه صلاحية "view users" (أي أنه مدير أو أدمن).
// -------------------------------------------------------------------------

// =========================================================================
// == مجموعة مسارات لوحة التحكم المحمية
// =========================================================================
Route::middleware(['auth:web', 'can:view users'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', function () {
        return view('admin.dashboard');

    })->name('dashboard');

    // --- إدارة المستخدمين ---
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role.update')->middleware('can:promote users');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy')
        ->middleware('can:delete users');
    // --- إدارة التصنيفات ---
    Route::resource('categories', CategoryController::class)->middleware('can:manage categories');
    // -->> إضافة مسارات التصنيفات الفرعية المتداخلة <<--
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
        Route::get('/trucks', [TruckController::class, 'index'])->name('trucks.index')->middleware('can:manage trucks');
        Route::get('/trucks/{truck}', [TruckController::class, 'show'])->name('trucks.show')->middleware('can:manage trucks');
        Route::patch('/trucks/{truck}/status', [TruckController::class, 'updateStatus'])->name('trucks.status.update')->middleware('can:manage trucks');

    // --- إدارة الحجوزات ---
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index')->middleware('can:manage trucks'); // نستخدم نفس صلاحية الشاحنات
    // -->> المسارات الجديدة لإدارة حالة الحجز <<--
    Route::get('/bookings/{booking}/status', [BookingController::class, 'showStatusPage'])->name('bookings.status')->middleware('can:manage trucks');
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status.update')->middleware('can:manage trucks');
    
});

// =========================================================================
// == مسارات المصادقة للوحة التحكم
// =========================================================================

// عرض صفحة تسجيل الدخول
Route::get('admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [LoginController::class, 'login']);
Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::get('access-denied', function () {
    return view('admin.access-denied');
})->name('access.denied');
Route::get('admin/register', [RegisterController::class, 'showRegistrationForm'])->name('admin.register');
