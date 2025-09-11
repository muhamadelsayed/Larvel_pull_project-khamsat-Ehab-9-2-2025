<?php

namespace App\Policies;

use App\Models\Truck;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TruckPolicy
{
    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Truck $truck
     * @return bool
     */
    public function update(User $user, Truck $truck): bool
    {
        // السماح بالتحديث فقط إذا كان معرّف المستخدم الحالي
        // يطابق معرّف المستخدم المرتبط بالشاحنة.
        return $user->id === $truck->user_id;
    }
    public function delete(User $user, Truck $truck): bool
{
    // السماح بالحذف فقط إذا كان المستخدم هو مالك الشاحنة
    return $user->id === $truck->user_id;
}
}