<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'otp',
        'expires_at',
        'used_at', // <-- إضافة الحقل الجديد هنا
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime', // <-- وإضافته هنا أيضاً
    ];
}