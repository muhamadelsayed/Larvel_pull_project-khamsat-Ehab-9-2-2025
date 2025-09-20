<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Truck extends Model {
    protected $fillable = [
        'user_id',
        'category_id',
        'sub_category_id',
        'name', // <-- إضافة جديدة
        'year_of_manufacture',
        'size',
        'model',
        'description',
        'additional_features',
        'video',
        'price_per_day',
        'price_per_hour',
        'work_start_time',
        'work_end_time',
        'pickup_location',
        'delivery_available',
        'delivery_price',
        'status',
    ];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subCategory(): BelongsTo { return $this->belongsTo(SubCategory::class); }
    public function images(): HasMany { return $this->hasMany(TruckImage::class); }

    public function bookings(): HasMany
{
    return $this->hasMany(Booking::class);
}

}