<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // تحديد ما إذا كان المستخدم الحالي هو العميل أم المالك
        $isOwner = $this->truck->user_id === auth()->id();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'start_datetime' => $this->start_datetime->format('Y-m-d H:i'),
            'end_datetime' => $this->end_datetime->format('Y-m-d H:i'),
            'total_price' => $this->total_price,
            // عرض بيانات الشاحنة
            'truck' => [
                'id' => $this->truck->id,
                'model' => $this->truck->model,
                'main_image' => $this->truck->images->first() ? asset('storage/' . $this->truck->images->first()->path) : null,
            ],
            // عرض بيانات الطرف الآخر
            'other_party' => $isOwner ? [
                'type' => 'customer',
                'name' => $this->customer->name,
                'profile_photo_url' => $this->customer->profile_photo_url,
            ] : [
                'type' => 'owner',
                'name' => $this->truck->user->name,
                'profile_photo_url' => $this->truck->user->profile_photo_url,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}