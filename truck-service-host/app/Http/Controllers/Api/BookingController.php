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
    public function index(Request $request)
        {
            $user = auth()->user();

            $request->validate([
                'status' => ['nullable', Rule::in(['pending', 'approved', 'confirmed', 'rejected', 'cancelled', 'completed'])],
                'type' => ['nullable', Rule::in(['incoming', 'outgoing'])],
            ]);

            $bookingsQuery = Booking::query()
                ->with(['truck.images', 'truck.category', 'truck.subCategory', 'customer', 'truck.user']) 
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
    public function approve(Booking $booking)
        {
            $this->authorize('approve', $booking);

            $booking->update(['status' => 'approved']);

            return response()->json([
                'message' => 'Booking has been approved. Awaiting payment.',
                'booking' => new BookingResource($booking)
            ]);
        }
    public function reject(Booking $booking)
    {
        $this->authorize('reject', $booking);

        $booking->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Booking has been rejected.',
            'booking' => new BookingResource($booking)
        ]);
    }
    public function cancel(Booking $booking)
    {
        $this->authorize(ability: 'cancel', arguments: $booking);

        $booking->update(['status' => 'cancelled']);
        return response()->json([
            'message' => 'Booking has been cancelled.',
            'booking' => new BookingResource($booking)
        ]);
    }

}