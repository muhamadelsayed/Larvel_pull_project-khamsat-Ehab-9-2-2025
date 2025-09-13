<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $guarded = [];

    // تحديد أنواع البيانات لتسهيل التعامل معها
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    // علاقة: الحجز يخص عميل واحد
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // علاقة: الحجز يخص شاحنة واحدة
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}