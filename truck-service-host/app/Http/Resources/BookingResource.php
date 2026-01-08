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
            'customer_name' => $this->customer->name,
              'truck' => [
                'id' => $this->truck->id,
                'name' => $this->truck->name,
                'model' => $this->truck->model,
                'owner_name' => $this->truck->user->name,
                'status' => $this->truck->status,
                'pickup_location' => $this->truck->pickup_location,
                'price_per_day' => $this->truck->price_per_day,
                'category' => $this->truck->category->name,
                'sub_category' => $this->truck->subCategory->name,
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