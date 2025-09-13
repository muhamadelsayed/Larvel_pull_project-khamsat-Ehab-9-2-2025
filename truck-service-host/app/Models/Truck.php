<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Truck extends Model {
    protected $guarded = [];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function subCategory(): BelongsTo { return $this->belongsTo(SubCategory::class); }
    public function images(): HasMany { return $this->hasMany(TruckImage::class); }

    public function bookings(): HasMany
{
    return $this->hasMany(Booking::class);
}

}