<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- الخطوة 1: قم باستيراد هذا
use Spatie\Permission\Traits\HasRoles; // <-- استيراد
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

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
        'blocked_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo_path',
        'identity_image',        // <-- إخفاء المسار الخام للهوية
        'driving_license_image', // <-- إخفاء المسار الخام للرخصة
    ];
    protected $appends = [
        'profile_photo_url', 
        'identity_image_url',        // <-- إلحاق الرابط الكامل للهوية
        'driving_license_image_url', // <-- إلحاق الرابط الكامل للرخصة
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'blocked_at' => 'datetime',
        'profile_photo_url' => 'string',
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
    if (!$this->profile_photo_path) {
        return "https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460__340.png";
    }

    if (Str::startsWith($this->profile_photo_path, 'http')) {
        return $this->profile_photo_path;
    }

    return asset('storage/' . $this->profile_photo_path);
    }
    public function getIdentityImageUrlAttribute(): ?string
    {
        if (!$this->identity_image) return null;
        
        return Str::startsWith($this->identity_image, 'http') 
            ? $this->identity_image 
            : asset('storage/' . $this->identity_image);
    }

    /**
     * رابط صورة رخصة القيادة
     */
    public function getDrivingLicenseImageUrlAttribute(): ?string
    {
        if (!$this->driving_license_image) return null;

        return Str::startsWith($this->driving_license_image, 'http') 
            ? $this->driving_license_image 
            : asset('storage/' . $this->driving_license_image);
    }
    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    public function notifications(): HasMany
    {
        // هذا يربط المستخدم بجدول الإشعارات المخصص
        return $this->hasMany(Notification::class);
    }

}