<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompleteExpiredBookings extends Command
{
    protected $signature = 'bookings:complete';
    protected $description = 'تحويل الحجوزات المنتهية إلى مكتملة وإرسال إشعارات';

    public function handle()
    {
        $notificationService = new NotificationService();
        
        // جلب الحجوزات المؤكدة (المدفوعة) التي تجاوزت وقت النهاية
        $expiredBookings = Booking::where('status', 'confirmed')
            ->where('end_datetime', '<=', Carbon::now())
            ->with(['truck', 'customer', 'truck.user'])
            ->get();

            /** @var \App\Models\Booking $booking */
        foreach ($expiredBookings as $booking) {
            // 1. تحديث الحالة
            $booking->update(['status' => 'completed']);

            // 2. إشعار العميل
            $notificationService->sendNotification(
                $booking->customer,
                "اكتمل الحجز بنجاح",
                "تم إنهاء حجزك للشاحنة ({$booking->truck->name}). شكراً لاستخدامك Bull Station.",
                ['type' => 'booking', 'booking_id' => (string)$booking->id]
            );

            // 3. إشعار صاحب الشاحنة
            $notificationService->sendNotification(
                $booking->truck->user,
                "حجز مكتمل",
                "انتهى وقت الحجز رقم #{$booking->id}. شاحنتك الآن جاهزة لموعد جديد.",
                ['type' => 'booking', 'booking_id' => (string)$booking->id]
            );
        }
    }
}