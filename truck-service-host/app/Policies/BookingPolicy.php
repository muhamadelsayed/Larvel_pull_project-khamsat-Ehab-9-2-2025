<?php
namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can approve a booking.
     * (المستخدم هو مالك الشاحنة والحجز قيد المراجعة)
     */
    public function approve(User $user, Booking $booking): bool
    {
        return $user->id === $booking->truck->user_id && $booking->status === 'pending';
    }

    /**
     * Determine whether the user can reject a booking.
     * (نفس شروط الموافقة)
     */
    public function reject(User $user, Booking $booking): bool
    {
        return $user->id === $booking->truck->user_id && $booking->status === 'pending';
    }

    /**
     * Determine whether the user (customer) can cancel a booking.
     * العميل فقط يمكنه إلغاء الحجز قبل تأكيده
     */
    public function cancel(User $user, Booking $booking): bool
    {
        // يمكن للعميل إلغاء الحجز إذا كان هو من أنشأ الحجز والحالة ليست confirmed , completed , rejected , cancelled
        
        return $user->id === $booking->customer_id && ! in_array($booking->status, ['confirmed', 'completed', 'rejected', 'cancelled']);
    }
}