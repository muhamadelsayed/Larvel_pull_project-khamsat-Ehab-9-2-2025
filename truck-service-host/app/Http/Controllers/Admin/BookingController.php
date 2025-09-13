<?php
    namespace App\Http\Controllers\Admin;

    use App\Http\Controllers\Controller;
    use App\Models\Booking;
    use Illuminate\Http\Request;

    class BookingController extends Controller
    {
        public function index(Request $request)
        {
            $bookings = Booking::with(['truck', 'customer'])
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate(20);
                
            return view('admin.bookings.index', compact('bookings'));
        }
    }