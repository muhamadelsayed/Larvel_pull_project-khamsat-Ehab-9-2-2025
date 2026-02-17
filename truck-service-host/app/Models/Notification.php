<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;
    
    // الأفضل هو استخدام guarded فارغة لأننا نسيطر بالكامل على المدخلات في الخدمة
    protected $guarded = [];

    // أو الطريقة الأكثر أمانًا:
    // protected $fillable = [
    //     'user_id',
    //     'title',
    //     'body',
    //     'data',
    //     'read_at',
    // ];

    // نحدد أن حقل 'data' يجب أن يُخزن ويُقرأ كـ JSON
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the user that the notification belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}