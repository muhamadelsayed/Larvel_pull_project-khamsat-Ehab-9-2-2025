<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // دالة مساعدة لجلب المفتاح الصحيح بناءً على حالة الإعدادات
    private function getTapSecretKey()
    {
        $mode = Setting::where('key', 'tap_payment_mode')->first()->value ?? 'test';
        return ($mode === 'live') ? env('TAP_LIVE_SECRET_KEY') : env('TAP_TEST_SECRET_KEY');
    }

    public function createTapCharge(Request $request)
    {
        $request->validate(['booking_id' => 'required|exists:bookings,id']);
        $booking = Booking::with('customer')->findOrFail($request->booking_id);

        if ($booking->status !== 'approved') {
            return response()->json(['message' => 'الحجز غير جاهز للدفع.'], 400);
        }

        $secretKey = $this->getTapSecretKey();

        $response = Http::withToken($secretKey)->post('https://api.tap.company/v2/charges', [
            'amount' => $booking->total_price,
            'currency' => env('TAP_CURRENCY', 'SAR'),
            'customer' => [
                'first_name' => $booking->customer->name,
                'email' => $booking->customer->email ?? 'customer@bull-station.com',
                'phone' => ['country_code' => '966', 'number' => str_replace('+966', '', $booking->customer->phone)]
            ],
            'source' => ['id' => 'src_all'],
            'redirect' => ['url' => route('payment.callback')],
            'post' => ['url' => 'https://bull-station.com/api/payment/webhook'], // يجب أن يكون رابطاً عاماً وحقيقياً
            'metadata' => ['booking_id' => (string)$booking->id]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $booking->update(['tap_charge_id' => $data['id']]);
            return response()->json(['payment_url' => $data['transaction']['url']]);
        }

        return response()->json(['message' => 'خطأ في الاتصال بـ Tap', 'error' => $response->json()], 500);
    }

    /**
     * الـ Callback: الصفحة التي يراها المستخدم في الـ WebView بعد الدفع
     */
      public function callback(Request $request)
    {
        $tap_id = $request->tap_id;
        $secretKey = $this->getTapSecretKey();

        // نتحقق من حالة العملية مباشرة من Tap
        $response = Http::withToken($secretKey)->get("https://api.tap.company/v2/charges/{$tap_id}");
        
        $status = 'pending';
        if ($response->successful()) {
            $data = $response->json();
            $status = $data['status']; // CAPTURED, FAILED, CANCELLED, etc.
        }

        // إرجاع الواجهة مع الحالة الحقيقية
        return view('admin.payments.callback_result', [
            'tap_id' => $tap_id,
            'status' => $status
        ]);
    }

    /**
     * الـ Webhook: السيرفر يتحدث مع السيرفر (هنا يحدث التأكيد الفعلي)
     */
    public function webhook(Request $request)
{
    $tap_id = $request->id;

    if (!$tap_id) return response()->json(['message' => 'No ID provided'], 400);

    // التحقق من سيرفر Tap مباشرة باستخدام الـ Secret Key الخاص بنا
    $secretKey = $this->getTapSecretKey();
    $response = Http::withToken($secretKey)->get("https://api.tap.company/v2/charges/{$tap_id}");

    if ($response->successful()) {
        $tapData = $response->json();
        
        // الآن نعتمد فقط على البيانات القادمة من سيرفر Tap مباشرة
        $status = $tapData['status'] ?? '';
        $bookingId = $tapData['metadata']['booking_id'] ?? null;

        if ($status === 'CAPTURED' && $bookingId) {
            $booking = Booking::find($bookingId);
            if ($booking && $booking->status !== 'confirmed') {
                $booking->update([
                    'status' => 'confirmed',
                    'payment_method' => $tapData['source']['payment_method'] ?? 'unknown'
                ]);
                
                // إرسال الإشعار
                (new NotificationService())->sendNotification(
                    $booking->truck->user,
                    "تم دفع الحجز!",
                    "تم دفع مبلغ الحجز رقم #{$booking->id}"
                );
            }
        }
    }

    return response()->json(['status' => 'verified'], 200);
}
}