<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- الخطوة 1: قم باستيراد هذا
use Spatie\Permission\Traits\HasRoles; // <-- استيراد
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'profile_photo_path',
        'account_type',
        'fleet_owner_code',
        'identity_image',
        'driving_license_image',
        'location',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
     public function trucks(): HasMany
    {
        // هذا السطر يخبر Laravel بأن المستخدم الواحد يمكن أن يمتلك العديد من الشاحنات
        return $this->hasMany(Truck::class);
    }
    public function bookingsAsCustomer(): HasMany
{
    return $this->hasMany(Booking::class, 'customer_id');
}

public function getProfilePhotoUrlAttribute(): string
{
    // إذا كان المسار يبدأ بـ http، فإنه رابط خارجي، أعده كما هو.
    if (Str::startsWith($this->profile_photo_path, 'http')) {
        return $this->profile_photo_path;
    }
    
    // وإلا، فهو مسار داخلي، قم ببناء الرابط باستخدام asset().
    // هذا سيجعل الكود جاهزًا عندما نسمح للمستخدم برفع صورته الخاصة.
    return asset('storage/' . $this->profile_photo_path);
}
}