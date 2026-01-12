<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <-- هذا هو السطر الذي كان ناقصًا

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

    public function showStatusPage(Booking $booking)
    {
        $booking->load(['truck', 'customer']);
        return view('admin.bookings.status', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        // 1. التحقق من صحة الحالة الجديدة المرسلة
        $validated = $request->validate([
            'status' => ['required', Rule::in(['completed', 'rejected'])]
        ]);

        $newStatus = $validated['status'];

        // 2. تطبيق منطق العمل الجديد
        // لا يمكن تغيير حالة حجز مكتمل أو مرفوض بالفعل (لأنها حالات نهائية)
        if (in_array($booking->status, ['completed', 'rejected'])) {
            return back()->with('error', 'لا يمكن تغيير حالة هذا الحجز لأنه في حالة نهائية بالفعل.');
        }

        // يمكن إكمال الحجز إذا كان pending, approved, أو confirmed
        $canBeCompleted = in_array($booking->status, ['pending', 'approved', 'confirmed']);
        if ($newStatus === 'completed' && !$canBeCompleted) {
            // هذا الشرط لن يتحقق غالبًا مع المنطق الجديد، ولكنه حماية إضافية
            return back()->with('error', "لا يمكن إكمال الحجز إلا إذا كانت حالته pending, approved, أو confirmed. الحالة الحالية هي: {$booking->status}.");
        }
        
        // يمكن رفض أي حجز ليس في حالة نهائية
        // لا حاجة لشرط إضافي هنا

        // 3. تحديث الحالة
        $booking->update(['status' => $newStatus]);

        return redirect()->route('admin.bookings.index')->with('success', "تم تحديث حالة الحجز بنجاح إلى '{$newStatus}'.");
    }
}