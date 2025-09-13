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
 *      description="Interactive API documentation for the Truck Service application.",
 *      @OA\Contact(
 *          email="masalim122006@gmail.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Main API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer"
 * )
 * 
 * @group Authentication
 * APIs for managing user authentication.
 */
class AuthController extends Controller
{

    /**
     * @OA\PathItem(
     *      path="/api/register",
     *      @OA\Post(
     *          operationId="registerUser",
     *          tags={"Authentication"},
     *          summary="Register a new user",
     *          description="Creates a new user account after successful validation of a Firebase ID token. The Firebase token must be sent in the Authorization header as a Bearer token.",
     *          @OA\RequestBody(
     *              required=true,
     *              @OA\MediaType(
     *                  mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                      required={"name", "phone", "password", "account_type"},
     *                      @OA\Property(property="name", type="string", example="Test User"),
     *                      @OA\Property(property="phone", type="string", example="+966512345678"),
     *                      @OA\Property(property="password", type="string", format="password", example="password123"),
     *                      @OA\Property(property="account_type", type="string", enum={"client", "truck_owner"}, example="truck_owner"),
     *                      @OA\Property(property="identity_image", type="string", format="binary"),
     *                      @OA\Property(property="driving_license_image", type="string", format="binary"),
     *                  )
     *              )
     *          ),
     *          @OA\Response(response=201, description="Successful registration"),
     *          @OA\Response(response=401, description="Invalid Firebase Token"),
     *          @OA\Response(response=422, description="Validation Error")
     *      )
     * )
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
     * @OA\PathItem(
     *      path="/api/login",
     *      @OA\Post(
     *          operationId="loginUser",
     *          tags={"Authentication"},
     *          summary="User Login",
     *          @OA\RequestBody(
     *              required=true,
     *              @OA\JsonContent(
     *                  required={"phone", "password"},
     *                  @OA\Property(property="phone", type="string", example="+966512345678"),
     *                  @OA\Property(property="password", type="string", format="password", example="password123"),
     *              )
     *          ),
     *          @OA\Response(response=200, description="Successful login"),
     *          @OA\Response(response=401, description="Unauthorized")
     *      )
     * )
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
     * @OA\PathItem(
     *      path="/api/update-location",
     *      @OA\Post(
     *          operationId="updateLocation",
     *          tags={"User"},
     *          summary="Update user location",
     *          description="Updates the authenticated user's location.",
     *          security={{"sanctum":{}}},
     *          @OA\RequestBody(
     *              required=true,
     *              @OA\JsonContent(
     *                  required={"location"},
     *                  @OA\Property(property="location", type="string", example="Riyadh, Saudi Arabia"),
     *              )
     *          ),
     *          @OA\Response(response=200, description="Location updated successfully"),
     *          @OA\Response(response=422, description="Validation Error")
     *      )
     * )
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

    /**
     * @OA\PathItem(
     *      path="/api/forgot-password",
     *      @OA\Post(
     *          operationId="forgotPassword",
     *          tags={"Authentication"},
     *          summary="Initiate password reset (send OTP)",
     *          description="Sends an OTP code to the user's phone for password reset. The phone number must exist in the system.",
     *          @OA\RequestBody(
     *              required=true,
     *              @OA\JsonContent(
     *                  required={"phone"},
     *                  @OA\Property(property="phone", type="string", example="+966512345678")
     *              )
     *          ),
     *          @OA\Response(response=200, description="OTP for password reset has been sent."),
     *          @OA\Response(response=422, description="Validation Error")
     *      )
     * )
     */
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
/**
 * @OA\Post(
 *     path="/reset-password",
 *     summary="Reset user password",
 *     description="Allows a user to reset their password using a valid token.",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"token","password"},
 *             @OA\Property(property="token", type="string", description="Password reset token"),
 *             @OA\Property(property="password", type="string", format="password", description="New password")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password reset successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Password has been reset successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid token or password",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Invalid token or password.")
 *         )
 *     )
 * )
 */
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