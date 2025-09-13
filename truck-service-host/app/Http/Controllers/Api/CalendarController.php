<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function getBookedDates(Truck $truck)
    {
        // جلب جميع الحجوزات المؤكدة لهذه الشاحنة
        $bookings = $truck->bookings()
                          ->where('status', 'confirmed')
                          ->get(['start_datetime', 'end_datetime']);
        
        // يمكن للتطبيق استخدام هذه البيانات لعرضها في تقويم
        return response()->json($bookings);
    }
}