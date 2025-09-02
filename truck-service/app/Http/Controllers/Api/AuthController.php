<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache; // استيراد الكاش

class AuthController extends Controller
{
    /**
     * الخطوة الأولى من التسجيل: التحقق من البيانات وتخزينها مؤقتًا.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users|max:255',
            'password' => 'required|string|min:8',
            'account_type' => ['required', Rule::in(['truck_owner', 'client'])],
            'fleet_owner_code' => 'nullable|string|max:255',
            'identity_image' => 'required_if:account_type,truck_owner|image|mimes:jpeg,png,jpg,gif|max:2048',
            'driving_license_image' => 'required_if:account_type,truck_owner|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // تجهيز بيانات المستخدم
        $userData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'account_type' => $request->account_type,
            'fleet_owner_code' => $request->fleet_owner_code,
        ];

        // التعامل مع رفع الملفات
        if ($request->hasFile('identity_image')) {
            $path = $request->file('identity_image')->store('identity_images', 'public');
            $userData['identity_image'] = $path;
        }

        if ($request->hasFile('driving_license_image')) {
            $path = $request->file('driving_license_image')->store('driving_licenses', 'public');
            $userData['driving_license_image'] = $path;
        }

        // تخزين البيانات في الكاش لمدة 10 دقائق
        // المفتاح سيكون رقم الهاتف لسهولة الوصول إليه
        Cache::put('registration_data_' . $request->phone, $userData, 600); // 600 ثانية = 10 دقائق

        // في التطبيق الحقيقي، هنا يتم إرسال الرمز 111111 عبر مزود خدمة الرسائل
        
        return response()->json([
            'message' => 'Verification code sent successfully. Please verify your phone.',
            'phone' => $request->phone
        ], 200);
    }

    /**
     * الخطوة الثانية: التحقق من رمز OTP وإنشاء الحساب.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // التحقق من الرمز المؤقت
        if ($request->otp !== '111111') {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        // سحب بيانات المستخدم من الكاش
        $userData = Cache::get('registration_data_' . $request->phone);

        if (!$userData) {
            return response()->json(['message' => 'Registration data not found or has expired.'], 404);
        }

        // إنشاء المستخدم في قاعدة البيانات
        $user = User::create($userData);

        // حذف البيانات من الكاش بعد استخدامها
        Cache::forget('registration_data_' . $request->phone);

        return response()->json(['message' => 'Account created successfully. You can now log in.'], 201);
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
}