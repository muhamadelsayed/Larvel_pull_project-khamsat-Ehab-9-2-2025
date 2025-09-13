<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Truck;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\BookingResource;
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
     * @OA\Get(
     *      path="/api/my-bookings",
     *      operationId="getMyBookings",
     *      tags={"Booking Management"},
     *      summary="Get the authenticated user's bookings",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending", "approved", "confirmed", "rejected", "cancelled", "completed"})),
     *      @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"incoming", "outgoing"})),
     *      @OA\Response(
     *          response=200,
     *          description="List of bookings for the authenticated user",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BookingResource")),
     *              @OA\Property(property="links", type="object", example={"first": "url", "last": "url", "prev": null, "next": "url"}),
     *              @OA\Property(property="meta", type="object", example={"current_page": 1, "from": 1, "last_page": 10, "path": "url", "per_page": 15, "to": 15, "total": 150})
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object", example={"status": {"The selected status is invalid."}})
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'confirmed', 'rejected', 'cancelled', 'completed'])],
            'type' => ['nullable', Rule::in(['incoming', 'outgoing'])],
        ]);

        $bookingsQuery = Booking::query()
            ->with(['truck.images', 'customer', 'truck.user'])
            ->latest();

        $type = $request->input('type');

        if ($type === 'incoming') {
            $bookingsQuery->whereHas('truck', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        } elseif ($type === 'outgoing') {
            $bookingsQuery->where('customer_id', $user->id);
        } else {
            $bookingsQuery->where(function ($query) use ($user) {
                $query->where('customer_id', $user->id)
                    ->orWhereHas('truck', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            });
        }

        if ($request->filled('status')) {
            $bookingsQuery->where('status', $request->status);
        }

        return BookingResource::collection($bookingsQuery->paginate(15));
    }

    /**
     * @OA\Post(
     *      path="/api/bookings",
     *      operationId="storeBooking",
     *      tags={"Booking Management"},
     *      summary="Create a new booking request",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"truck_id", "start_datetime", "end_datetime", "days", "hours", "needs_delivery"},
     *              @OA\Property(property="truck_id", type="integer"),
     *              @OA\Property(property="start_datetime", type="string", format="date-time", example="2025-10-20 08:00:00"),
     *              @OA\Property(property="end_datetime", type="string", format="date-time", example="2025-10-22 17:00:00"),
     *              @OA\Property(property="days", type="integer", example="2"),
     *              @OA\Property(property="hours", type="integer", example="9"),
     *              @OA\Property(property="needs_delivery", type="boolean", example="true"),
     *          )
     *      ),
    *      @OA\Response(
    *          response=201,
    *          description="Booking request sent successfully",
    *          @OA\JsonContent(
    *              type="object",
    *              @OA\Property(property="message", type="string", example="Booking request sent successfully. Awaiting owner approval."),
    *              @OA\Property(property="booking_id", type="integer", example=123),
    *              @OA\Property(
    *                  property="booking_details",
    *                  type="object",
    *                  @OA\Property(property="total_price", type="number", format="float", example=350.5),
    *                  @OA\Property(property="status", type="string", example="pending")
    *              )
    *          )
    *      ),
    *      @OA\Response(
    *          response=409,
    *          description="Conflict, truck not available",
    *          @OA\JsonContent(
    *              type="object",
    *              @OA\Property(property="message", type="string", example="Sorry, this truck is no longer available for the selected dates.")
    *          )
    *      ),
    *      @OA\Response(
    *          response=401,
    *          description="Unauthenticated",
    *          @OA\JsonContent(
    *              type="object",
    *              @OA\Property(property="message", type="string", example="Unauthenticated.")
    *          )
    *      ),
    *      @OA\Response(
    *          response=422,
    *          description="Validation error",
    *          @OA\JsonContent(
    *              type="object",
    *              @OA\Property(property="message", type="string", example="The given data was invalid."),
    *              @OA\Property(
    *                  property="errors",
    *                  type="object",
    *                  example={
    *                      "truck_id": {"The selected truck id is invalid."},
    *                      "start_datetime": {"The start datetime is not a valid date."},
    *                      "end_datetime": {"The end datetime must be a date after or equal to start datetime."},
    *                      "days": {"The days must be at least 0."},
    *                      "hours": {"The hours must be at least 0."},
    *                      "needs_delivery": {"The needs delivery field must be true or false."}
    *                  }
    *              )
    *          )
    *      ),
     * )
     */
    public function store(Request $request)
    {
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

        $isBooked = $truck->bookings()
            ->whereIn('status', ['confirmed', 'approved'])
            ->where(function ($query) use ($start, $end) {
                $query->where(fn($q) => $q->where('start_datetime', '<', $end)->where('end_datetime', '>', $start));
            })->exists();

        if ($isBooked) {
            return response()->json(['message' => 'Sorry, this truck is no longer available for the selected dates.'], 409);
        }

        $basePrice = ($validated['days'] * $truck->price_per_day) + ($validated['hours'] * $truck->price_per_hour);
        $deliveryPrice = ($validated['needs_delivery'] && $truck->delivery_available) ? $truck->delivery_price : 0;
        $totalPrice = $basePrice + $deliveryPrice;

        $booking = Booking::create([
            'truck_id' => $truck->id,
            'customer_id' => auth()->id(),
            'start_datetime' => $start,
            'end_datetime' => $end,
            'base_price' => $basePrice,
            'delivery_price' => $deliveryPrice,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking request sent successfully. Awaiting owner approval.',
            'booking_id' => $booking->id,
            'booking_details' => [
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]
        ], 201);
    }

    /**
     * @OA\Post(
     *      path="/api/my-bookings/{booking}/approve",
     *      operationId="approveBooking",
     *      tags={"Booking Management"},
     *      summary="Approve a booking request (Truck Owner)",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(name="booking", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="Booking has been approved. Awaiting payment.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Booking has been approved. Awaiting payment."),
     *              @OA\Property(property="booking", ref="#/components/schemas/BookingResource")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *          )
     *      ),
     * )
     */
    public function approve(Booking $booking)
    {
        $this->authorize('approve', $booking);

        $booking->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Booking has been approved. Awaiting payment.',
            'booking' => new BookingResource($booking)
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/my-bookings/{booking}/reject",
     *      operationId="rejectBooking",
     *      tags={"Booking Management"},
     *      summary="Reject a booking request (Truck Owner)",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(name="booking", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="Booking has been rejected.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Booking has been rejected."),
     *              @OA\Property(property="booking", ref="#/components/schemas/BookingResource")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *          )
     *      ),
     * )
     */
    public function reject(Booking $booking)
    {
        $this->authorize('reject', $booking);

        $booking->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Booking has been rejected.',
            'booking' => new BookingResource($booking)
        ]);
    }

}