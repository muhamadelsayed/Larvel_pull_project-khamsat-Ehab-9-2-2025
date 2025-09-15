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

    /**
     * @OA\PathItem(
     *      path="/api/register",
     *      @OA\Post(
     *          operationId="registerUser",
     *          tags={"Authentication"},
     *          summary="Register a new user",
     *          description="Creates a new user account after validating a Firebase ID token (sent as Bearer in the Authorization header).",
     *          security={{"bearerAuth": {}}},
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
     *                      @OA\Property(property="identity_image", type="string", format="binary", description="Required if account_type is truck_owner"),
     *                      @OA\Property(property="driving_license_image", type="string", format="binary", description="Required if account_type is truck_owner")
     *                  )
     *              )
     *          ),
     *          @OA\Response(
     *              response=201,
     *              description="Successful registration",
     *              @OA\JsonContent(
     *                  @OA\Property(property="message", type="string", example="User registered successfully."),
     *                  @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                  @OA\Property(property="token_type", type="string", example="Bearer"),
     *                  @OA\Property(property="user", ref="#/components/schemas/User")
     *              )
     *          ),
     *          @OA\Response(
     *              response=401,
     *              description="Invalid or missing Firebase token",
     *              @OA\JsonContent(
     *                  @OA\Property(property="message", type="string", example="Firebase ID Token is missing.")
     *              )
     *          ),
     *          @OA\Response(
     *              response=403,
     *              description="Phone number mismatch",
     *              @OA\JsonContent(
     *                  @OA\Property(property="message", type="string", example="Phone number does not match the verified token.")
     *              )
     *          ),
     *          @OA\Response(
     *              response=422,
     *              description="Validation Error",
     *              @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *          )
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
    *          @OA\Response(
    *              response=200,
    *              description="Login successful",
    *              @OA\JsonContent(
    *                  @OA\Property(property="message", type="string", example="Login successful"),
    *                  @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
    *                  @OA\Property(property="token_type", type="string", example="Bearer"),
    *                  @OA\Property(property="user", ref="#/components/schemas/User")
    *              )
    *          ),
    *          @OA\Response(
    *              response=401,
    *              description="Unauthorized",
    *              @OA\JsonContent(
    *                  @OA\Property(property="error", type="string", example="The provided credentials do not match our records.")
    *              )
    *          ),
    *          @OA\Response(
    *              response=422,
    *              description="Validation Error",
    *              @OA\JsonContent(
    *                  @OA\Property(property="phone", type="array", @OA\Items(type="string", example="The phone field is required.")),
    *                  @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password field is required."))
    *              )
    *          )
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
    *          @OA\Response(
    *              response=200,
    *              description="Location updated successfully",
    *              @OA\JsonContent(
    *                  @OA\Property(property="message", type="string", example="Location updated successfully")
    *              )
    *          ),
    *          @OA\Response(
    *              response=422,
    *              description="Validation Error",
    *              @OA\JsonContent(
    *                  @OA\Property(property="location", type="array", @OA\Items(type="string", example="The location field is required."))
    *              )
    *          )
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
     *          summary="Check phone for password reset",
     *          description="Validates that the provided phone number exists. If it exists, the client should proceed with Firebase OTP verification. This endpoint does not send the OTP.",
     *          @OA\RequestBody(
     *              required=true,
     *              @OA\JsonContent(
     *                  required={"phone"},
     *                  @OA\Property(property="phone", type="string", example="+966512345678")
     *              )
     *          ),
     *          @OA\Response(
     *              response=200,
     *              description="User found. Proceed with OTP verification.",
     *              @OA\JsonContent(
     *                  @OA\Property(property="message", type="string", example="User found. Proceed with OTP verification.")
     *              )
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="User not found",
     *              @OA\JsonContent(
     *                  @OA\Property(property="message", type="string", example="This phone number is not registered with us.")
     *              )
     *          ),
     *          @OA\Response(
     *              response=422,
     *              description="Validation Error",
     *              @OA\JsonContent(
     *                  @OA\Property(property="phone", type="array", @OA\Items(type="string", example="The phone field is required."))
     *              )
     *          )
     *      )
     * )
     */
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
/**
 * @OA\Post(
 *   path="/api/reset-password",
 *   summary="Reset user password using Firebase phone verification",
 *   description="Verifies a Firebase ID token, ensures the token phone_number matches the provided phone, and updates the user's password.",
 *   operationId="resetPasswordWithFirebasePhone",
 *   tags={"Authentication"},
 *   security={{"bearerAuth": {}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       type="object",
 *       required={"phone","password","password_confirmation"},
 *       @OA\Property(property="phone", type="string", description="E.164 formatted phone number associated with the Firebase user"),
 *       @OA\Property(property="password", type="string", format="password", minLength=8, description="New password (min 8 characters)"),
 *       @OA\Property(property="password_confirmation", type="string", format="password", description="Must match the password field"),
 *       example={
 *         "phone": "+15551234567",
 *         "password": "newStrongP@ssw0rd",
 *         "password_confirmation": "newStrongP@ssw0rd"
 *       }
 *     )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Password has been updated successfully.",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="message", type="string", example="Password has been updated successfully.")
 *     )
 *   ),
 *   @OA\Response(
 *     response=401,
 *     description="Unauthorized - Missing or invalid Firebase ID Token.",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="message", type="string", example="Invalid Firebase ID Token: <reason>")
 *     )
 *   ),
 *   @OA\Response(
 *     response=403,
 *     description="Forbidden - Phone number does not match the verified token.",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="message", type="string", example="Phone number does not match the verified token.")
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Not Found - User not found.",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="message", type="string", example="User not found.")
 *     )
 *   ),
 *   @OA\Response(
 *     response=422,
 *     description="Unprocessable Entity - Validation errors.",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\AdditionalProperties(
 *         type="array",
 *         @OA\Items(type="string")
 *       ),
 *       example={
 *         "phone": {"The phone field is required."},
 *         "password": {"The password must be at least 8 characters."},
 *         "password_confirmation": {"The password confirmation does not match."}
 *       }
 *     )
 *   )
 * )
 */
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

}