<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache; // استيراد الكاش
use App\Models\PasswordResetOtp;
use Kreait\Firebase\Factory; // <-- إضافة جديدة
use Kreait\Firebase\Auth as FirebaseAuth; // <-- إضافة جديدة
use Exception; // <-- إضافة جديدة

class AuthController extends Controller
{
    /**
     * الخطوة الأولى من التسجيل: التحقق من البيانات وتخزينها مؤقتًا.
     */
    public function register(Request $request)
    {
        // 1. التحقق من صحة بيانات الفورم المرسلة
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone|max:255',
            'password' => 'required|string|min:8',
            'account_type' => ['required', Rule::in(['truck_owner', 'client'])],
            'identity_image' => 'required_if:account_type,truck_owner|image|mimes:jpeg,png,jpg,gif|max:2048',
            'driving_license_image' => 'required_if:account_type,truck_owner|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fleet_owner_code' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. التحقق من وجود Firebase ID Token في الهيدر
        $firebaseToken = $request->bearerToken();
        if (!$firebaseToken) {
            return response()->json(['message' => 'Firebase ID Token is missing.'], 401);
        }

        // 3. التحقق من صحة الـ Token مع Firebase
        try {
            $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials.file'));
            $auth = $factory->createAuth();
            $verifiedIdToken = $auth->verifyIdToken($firebaseToken);
        } catch (Exception $e) {
            // إذا فشل التحقق (Token غير صالح، منتهي الصلاحية، ...إلخ)
            return response()->json(['message' => 'Invalid Firebase ID Token: ' . $e->getMessage()], 401);
        }
        
        // 4. (اختياري ولكن موصى به) التأكد من أن رقم الهاتف في الـ Token يطابق الرقم في الفورم
        $firebasePhone = $verifiedIdToken->claims()->get('phone_number');
        if ($firebasePhone !== $request->phone) {
            return response()->json(['message' => 'Phone number does not match the verified token.'], 403);
        }
        
        // 5. إذا كان كل شيء صحيحًا، قم بإنشاء الحساب مباشرة
        $userData = $request->except(['password', 'identity_image', 'driving_license_image']);
        $userData['password'] = Hash::make($request->password);

        if ($request->hasFile('identity_image')) {
            $userData['identity_image'] = $request->file('identity_image')->store('identity_images', 'public');
        }
        if ($request->hasFile('driving_license_image')) {
            $userData['driving_license_image'] = $request->file('driving_license_image')->store('driving_licenses', 'public');
        }

        $user = User::create($userData);

        // 6. إنشاء توكين Sanctum وإرجاعه للمستخدم لتسجيل الدخول فورًا
        $sanctumToken = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'access_token' => $sanctumToken,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }
    /**
     * تسجيل الدخول.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'The provided credentials do not match our records.'], 401);
        }
        
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    /**
     * تحديث موقع المستخدم (يتطلب تسجيل الدخول).
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = $request->user(); // الوصول للمستخدم المصادق عليه
        $user->location = $request->location;
        $user->save();

        return response()->json(['message' => 'Location updated successfully'], 200);
    }

    // reset password section
    public function forgotPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => 'required|string|exists:users,phone',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // الرمز المؤقت
    $otpCode = '111111';

    // -- التغيير يبدأ هنا --
    // إنشاء سجل جديد في جدول الـ OTP
    PasswordResetOtp::create([
        'phone' => $request->phone,
        'otp' => Hash::make($otpCode), // تشفير الرمز قبل الحفظ
        'expires_at' => now()->addMinutes(5), // تحديد انتهاء الصلاحية بعد 5 دقائق
    ]);
    // -- التغيير ينتهي هنا --

    return response()->json([
        'message' => 'OTP for password reset has been sent.',
        'phone' => $request->phone
    ], 200);
}
public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => 'required|string|exists:users,phone',
        'otp' => 'required|string|size:6',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // البحث عن أحدث رمز تم إرساله لهذا الرقم ولم يتم استخدامه بعد
    $otpRecord = PasswordResetOtp::where('phone', $request->phone)
                                  ->whereNull('used_at') // <-- شرط مهم للبحث فقط في الرموز غير المستخدمة
                                  ->latest()
                                  ->first();

    // التحقق المبدئي
    if (!$otpRecord) {
        return response()->json(['message' => 'OTP not found. Please request a new one.'], 404);
    }
    
    // التحقق من انتهاء الصلاحية
    if (now()->isAfter($otpRecord->expires_at)) {
        return response()->json(['message' => 'This OTP has expired. Please request a new one.'], 400);
    }

    // التحقق من تطابق الرمز
    if (!Hash::check($request->otp, $otpRecord->otp)) {
        return response()->json(['message' => 'The provided OTP is incorrect.'], 400);
    }

    // --- إذا كانت كل الشروط صحيحة ---

    // 1. تحديث كلمة المرور للمستخدم
    $user = User::where('phone', $request->phone)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    // 2. تحديث سجل الـ OTP لتسجيل وقت استخدامه (بدلاً من حذفه)
    $otpRecord->update(['used_at' => now()]);

    return response()->json(['message' => 'Password has been updated successfully.'], 200);
}

}