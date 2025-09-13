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
}