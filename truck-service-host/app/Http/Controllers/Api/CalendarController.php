<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function getBookedDates(Truck $truck)
    {
       
        $bookings = $truck->bookings()
                          ->whereIn('status', ['confirmed', 'approved'])
                          ->get(['start_datetime', 'end_datetime']);
        
        // يمكن للتطبيق استخدام هذه البيانات لعرضها في تقويم
        return response()->json($bookings);
    }
}