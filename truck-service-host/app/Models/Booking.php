<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    // استبدلنا guarded بـ fillable لتحديد الحقول المسموح بها فقط (أكثر أماناً)
    protected $fillable = [
        'truck_id',
        'customer_id',
        'start_datetime',
        'end_datetime',
        'base_price',
        'delivery_price',
        'total_price',
        'status',
        'tap_charge_id',   // الحقل الجديد لربط عملية الدفع
        'payment_method',  // الحقل الجديد لتخزين وسيلة الدفع (مدى، فيزا، الخ)
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}