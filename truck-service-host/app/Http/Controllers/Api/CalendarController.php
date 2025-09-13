<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function getBookedDates(Truck $truck)
    {
        /**
         * @OA\Get(
         *     path="/api/trucks/{truck}/booked-dates",
         *     summary="Get booked dates for a truck",
         *     description="Returns a list of confirmed bookings for the specified truck.",
         *     tags={"Calendar"},
         *     @OA\Parameter(
         *         name="truck",
         *         in="path",
         *         description="ID of the truck",
         *         required=true,
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of booked dates",
         *         @OA\JsonContent(
         *             type="array",
         *             @OA\Items(
         *                 type="object",
         *                 @OA\Property(property="start_datetime", type="string", format="date-time", example="2024-06-10T08:00:00"),
         *                 @OA\Property(property="end_datetime", type="string", format="date-time", example="2024-06-10T12:00:00")
         *             )
         *         )
         *     )
         * )
         */
        $bookings = $truck->bookings()
                          ->where('status', 'confirmed')
                          ->get(['start_datetime', 'end_datetime']);
        
        // يمكن للتطبيق استخدام هذه البيانات لعرضها في تقويم
        return response()->json($bookings);
    }
}