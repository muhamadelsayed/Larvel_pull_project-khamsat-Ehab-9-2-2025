<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Truck;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\BookingResource; // <-- إضافة
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

/**
 * @group Booking Management
 * APIs for creating and managing bookings.
 */

class BookingController extends Controller
{
    use AuthorizesRequests;
     /**
     * @OA\PathItem(
     *      path="/api/my-bookings",
     *      @OA\Get(
     *          operationId="getMyBookings",
     *          tags={"Booking Management"},
     *          summary="Get the authenticated user's bookings",
     *          security={{"bearerAuth":{}}},
     *          @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending", "approved", "confirmed", "rejected", "cancelled", "completed"})),
     *          @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"incoming", "outgoing"})),
     *          @OA\Response(response=200, description="Successful operation"),
     *      )
     * )
     */
public function index(Request $request)
{
    $user = auth()->user();

    // 1. التحقق من صحة الفلاتر
    $request->validate([
        'status' => ['nullable', Rule::in(['pending', 'approved', 'confirmed', 'rejected', 'cancelled', 'completed'])],
        'type' => ['nullable', Rule::in(['incoming', 'outgoing'])], // incoming: طلبات على شاحناتي, outgoing: حجوزاتي
    ]);

    // 2. بناء الاستعلام الأساسي
    $bookingsQuery = Booking::query()
        ->with(['truck.images', 'customer', 'truck.user'])
        ->latest();

    // 3. تطبيق فلتر النوع (وارد أم صادر)
    $type = $request->input('type');

    if ($type === 'incoming') {
        // جلب الحجوزات على الشاحنات التي يملكها المستخدم فقط
        $bookingsQuery->whereHas('truck', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });
    } elseif ($type === 'outgoing') {
        // جلب الحجوزات التي قام بها المستخدم كعميل فقط
        $bookingsQuery->where('customer_id', $user->id);
    } else {
        // إذا لم يتم تحديد النوع، جلب كليهما
        $bookingsQuery->where(function ($query) use ($user) {
            $query->where('customer_id', $user->id)
                ->orWhereHas('truck', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        });
    }

    // 4. تطبيق فلتر الحالة إذا تم إرساله
    if ($request->filled('status')) {
        $bookingsQuery->where('status', $request->status);
    }
    
    // 5. إرجاع النتائج مع pagination
    return BookingResource::collection($bookingsQuery->paginate(15));
}
    /**
     * @OA\PathItem(
     *      path="/api/bookings",
     *      @OA\Post(
     *          operationId="storeBooking",
     *          tags={"Booking Management"},
     *          summary="Create a new booking request",
     *          security={{"bearerAuth":{}}},
     *          @OA\RequestBody(
     *              required=true,
     *              @OA\JsonContent(
     *                  required={"truck_id", "start_datetime", "end_datetime", "days", "hours", "needs_delivery"},
     *                  @OA\Property(property="truck_id", type="integer"),
     *                  @OA\Property(property="start_datetime", type="string", format="date-time", example="2025-10-20 08:00:00"),
     *                  @OA\Property(property="end_datetime", type="string", format="date-time", example="2025-10-22 17:00:00"),
     *                  @OA\Property(property="days", type="integer", example="2"),
     *                  @OA\Property(property="hours", type="integer", example="9"),
     *                  @OA\Property(property="needs_delivery", type="boolean", example="true"),
     *              )
     *          ),
     *          @OA\Response(response=201, description="Booking request sent successfully"),
     *          @OA\Response(response=409, description="Conflict, truck not available"),
     *      )
     * )
     */
    public function store(Request $request)
    {
        // 1. التحقق من صحة المدخلات
        $validated = $request->validate([
            'truck_id' => 'required|exists:trucks,id',
            'start_datetime' => 'required|date|after_or_equal:today',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'days' => 'required|integer|min:0',
            'hours' => 'required|integer|min:0',
            'needs_delivery' => 'required|boolean',
        ]);

        $truck = Truck::findOrFail($validated['truck_id']);
        $start = Carbon::parse($validated['start_datetime']);
        $end = Carbon::parse($validated['end_datetime']);
        
        // 2. التحقق من التوفر (لا يزال ضروريًا كطبقة أمان أخيرة)
        $isBooked = $truck->bookings()
            ->whereIn('status', ['confirmed', 'approved']) // التحقق من الحجوزات المؤكدة أو التي بانتظار الدفع
            ->where(function ($query) use ($start, $end) {
                $query->where(fn($q) => $q->where('start_datetime', '<', $end)->where('end_datetime', '>', $start));
            })->exists();

        if ($isBooked) {
            return response()->json(['message' => 'Sorry, this truck is no longer available for the selected dates.'], 409); // 409 Conflict
        }

        // 3. حساب السعر بدقة بناءً على البيانات المرسلة
        $basePrice = ($validated['days'] * $truck->price_per_day) + ($validated['hours'] * $truck->price_per_hour);
        $deliveryPrice = ($validated['needs_delivery'] && $truck->delivery_available) ? $truck->delivery_price : 0;
        $totalPrice = $basePrice + $deliveryPrice;

        // 4. إنشاء سجل الحجز في قاعدة البيانات
        $booking = Booking::create([
            'truck_id' => $truck->id,
            'customer_id' => auth()->id(), // المستخدم الحالي هو العميل
            'start_datetime' => $start,
            'end_datetime' => $end,
            'base_price' => $basePrice,
            'delivery_price' => $deliveryPrice,
            'total_price' => $totalPrice,
            'status' => 'pending', // الحالة الافتراضية هي "قيد المراجعة"
        ]);

        // (مستقبلاً، هنا يتم إرسال إشعار لصاحب الشاحنة)

        return response()->json([
            'message' => 'Booking request sent successfully. Awaiting owner approval.',
            'booking_id' => $booking->id,
            'booking_details' => [
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]
        ], 201); // 201 Created
    }
      /**
     * @OA\PathItem(
     *      path="/api/my-bookings/{booking}/approve",
     *      @OA\Post(
     *          operationId="approveBooking",
     *          tags={"Booking Management"},
     *          summary="Approve a booking request (Truck Owner)",
     *          security={{"bearerAuth":{}}},
     *          @OA\Parameter(name="booking", in="path", required=true, @OA\Schema(type="integer")),
     *          @OA\Response(response=200, description="Booking approved"),
     *          @OA\Response(response=403, description="Forbidden"),
     *      )
     * )
     */
    public function approve(Booking $booking)
    {
        // التحقق من الصلاحية باستخدام الـ Policy
        $this->authorize('approve', $booking);
        
        // تحديث حالة الحجز
        $booking->update(['status' => 'approved']);

        // (مستقبلاً، هنا يتم إرسال إشعار للعميل)

        return response()->json([
            'message' => 'Booking has been approved. Awaiting payment.',
            'booking' => new BookingResource($booking)
        ]);
    }

     /**
     * @OA\PathItem(
     *      path="/api/my-bookings/{booking}/reject",
     *      @OA\Post(
     *          operationId="rejectBooking",
     *          tags={"Booking Management"},
     *          summary="Reject a booking request (Truck Owner)",
     *          security={{"bearerAuth":{}}},
     *          @OA\Parameter(name="booking", in="path", required=true, @OA\Schema(type="integer")),
     *          @OA\Response(response=200, description="Booking rejected"),
     *          @OA\Response(response=403, description="Forbidden"),
     *      )
     * )
     */
    public function reject(Booking $booking)
    {
        // التحقق من الصلاحية باستخدام الـ Policy
        $this->authorize('reject', $booking);

        $booking->update(['status' => 'rejected']);

        // (مستقبلاً، هنا يتم إرسال إشعار للعميل)

        return response()->json([
            'message' => 'Booking has been rejected.',
            'booking' => new BookingResource($booking)
        ]);
    }

}