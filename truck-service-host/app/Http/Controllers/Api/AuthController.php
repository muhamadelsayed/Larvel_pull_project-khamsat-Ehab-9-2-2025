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

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Truck Service API Documentation",
 *      description="Interactive API documentation for the Truck Service application. Developed by Mohammed Sleem.",
 *      @OA\Contact(email="dev.mohammed@example.com")
 * )
 * @OA\Server(url=L5_SWAGGER_CONST_HOST, description="Main API Server")
 * @OA\SecurityScheme(securityScheme="bearerAuth", type="http", scheme="bearer")
 *
 * @OA\Schema(
 *     schema="SuccessMessage",
 *     title="Success Message",
 *     @OA\Property(property="message", type="string", example="Operation was successful.")
 * )
 * @OA\Schema(
 *     schema="ValidationError",
 *     title="Validation Error",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(property="errors", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     title="User Model",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="account_type", type="string", enum={"client", "truck_owner"})
 * )
 *
 * @OA\Schema(
 *     schema="TruckResource",
 *     title="Truck Resource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="status", type="string", enum={"pending", "active", "inactive"}),
 *     @OA\Property(property="model", type="string"),
 *     @OA\Property(property="year_of_manufacture", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="price_per_day", type="string", format="decimal"),
 *     @OA\Property(property="price_per_hour", type="string", format="decimal"),
 *     @OA\Property(property="work_hours", type="string", example="08:00:00 - 18:00:00"),
 *     @OA\Property(property="pickup_location", type="string"),
 *     @OA\Property(property="delivery_price", type="string", format="decimal"),
 *     @OA\Property(property="owner", type="object", ref="#/components/schemas/User"),
 *     @OA\Property(property="category", type="string"),
 *     @OA\Property(property="sub_category", type="string"),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string", format="url")),
 *     @OA\Property(property="video", type="string", format="url")
 * )
 *
 * @OA\Schema(
 *     schema="MyTruckResource",
 *     title="My Truck Resource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="model", type="string"),
 *     @OA\Property(property="status", type="string", enum={"pending", "active", "inactive"}),
 *     @OA\Property(property="price_per_day", type="string", format="decimal"),
 *     @OA\Property(property="category", type="string"),
 *     @OA\Property(property="main_image", type="string", format="url"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="BookingResource",
 *     title="Booking Resource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="start_datetime", type="string", format="date-time"),
 *     @OA\Property(property="end_datetime", type="string", format="date-time"),
 *     @OA\Property(property="total_price", type="string", format="decimal"),
 *     @OA\Property(property="truck", type="object", ref="#/components/schemas/MyTruckResource"),
 *     @OA\Property(property="other_party", type="object")
 * )
 * @group Authentication
 * APIs for managing user authentication.
 */
class AuthController extends Controller
{
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
public function updateLocation(Request $request)
    {
        // 1. التحقق من صحة المدخلات
        $validator = Validator::make($request->all(), [
            'location' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. الحصول على المستخدم المصادق عليه
        $user = $request->user();
        
        // 3. تحديث الموقع
        $user->location = $request->location;
        
        // 4. حفظ التغييرات
        $user->save();

        return response()->json([
            'message' => 'User data updated successfully.',
            'user' => $user->fresh() // إرجاع بيانات المستخدم المحدثة
        ], 200);
    }

public function forgotPassword(Request $request)
    {
        // 1. التحقق من أن رقم الهاتف تم إرساله
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. التحقق من أن هذا الرقم مسجل لدينا بالفعل
        $userExists = User::where('phone', $request->phone)->exists();

        if ($userExists) {
            // إذا كان المستخدم موجودًا، أعد استجابة نجاح للسماح للتطبيق
            // بالبدء في عملية إرسال الـ OTP عبر Firebase.
            return response()->json(['message' => 'User found. Proceed with OTP verification.'], 200);
        } else {
            // إذا لم يكن المستخدم موجودًا، أعد رسالة خطأ واضحة.
            return response()->json(['message' => 'This phone number is not registered with us.'], 404);
        }
    }
public function resetPassword(Request $request)
    {
        // 1. التحقق من صحة كلمة المرور الجديدة
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
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
            return response()->json(['message' => 'Invalid Firebase ID Token: ' . $e->getMessage()], 401);
        }
        
        // 4. التأكد من أن رقم الهاتف في الـ Token يطابق الرقم في الفورم
        $firebasePhone = $verifiedIdToken->claims()->get('phone_number');
        if ($firebasePhone !== $request->phone) {
            return response()->json(['message' => 'Phone number does not match the verified token.'], 403);
        }

        // 5. العثور على المستخدم وتحديث كلمة المرور
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            // هذا التحقق احترازي، حيث أننا تحققنا منه في الخطوة الأولى
            return response()->json(['message' => 'User not found.'], 404);
        }
        
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password has been updated successfully.'], 200);
    }

    public function update_profile(Request $request)
    {
        // 1. الحصول على المستخدم المصادق عليه
        $user = $request->user();

        // 2. التحقق من صحة المدخلات
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'password' => 'sometimes|required|string|min:8|confirmed',
            'location' => 'sometimes|required|string|max:255',
            'driving_license_image' => 'sometimes|required|image|mimes:jpeg,png,jpg|max:2048',
            'identity_image' => 'sometimes|required|image|mimes:jpeg,png,jpg|max:2048',
            'profile_photo' => 'sometimes|required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 3. تحديث الحقول النصية (إذا كانت موجودة)
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('password')) { // filled() يتحقق من وجوده وأنه ليس فارغًا
            $user->password = Hash::make($request->password);
        }
        if ($request->has('location')) {
            $user->location = $request->location;
        }

        // 4. تحديث الصور (إذا كانت موجودة)
        if ($request->hasFile('driving_license_image')) {
            // استخدام "public" disk الذي قمنا بإعداده لحفظ الملفات في public/storage
            $path = $request->file('driving_license_image')->store('driving_licenses', 'public');
            $user->driving_license_image = $path;
        }
        if ($request->hasFile('identity_image')) {
            $path = $request->file('identity_image')->store('identity_images', 'public');
            $user->identity_image = $path;
        }
        if ($request->hasFile('profile_photo')) {
            // -->> الإصلاح الرئيسي هنا <<--
            // الحفظ في العمود الصحيح 'profile_photo_path'
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo_path = $path;
        }

        // 5. الحفظ الذكي (فقط إذا كان هناك تغييرات)
        if ($user->isDirty()) { // <-- استخدام isDirty() الصحيحة
            $user->save();
        }

        // 6. إرجاع استجابة نظيفة ومحدثة
        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh()->only([ // fresh() لإعادة تحميل النموذج من قاعدة البيانات
                'id', 'name', 'phone', 'account_type', 'location', 'profile_photo_url'
            ])
        ], 200);
    }
}