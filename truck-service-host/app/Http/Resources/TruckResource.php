<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TruckResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,                             // <-- تم الإضافة
            'status' => $this->status,
            'model' => $this->model,
            'size' => $this->size,                               // <-- تم الإضافة
            'year_of_manufacture' => $this->year_of_manufacture,
            'description' => $this->description,
            'additional_features' => $this->additional_features,    // <-- تم الإضافة
            'price_per_day' => $this->price_per_day,
            'price_per_hour' => $this->price_per_hour,
            'work_hours' => $this->work_start_time . ' - ' . $this->work_end_time,
            'pickup_location' => $this->pickup_location,
            'delivery_available' => (bool) $this->delivery_available, // <-- تم الإضافة (مع تحويلها لـ boolean)
            'delivery_price' => $this->delivery_price,
            'owner' => [
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'profile_photo_url' => $this->user->profile_photo_url, // <-- تم الإضافة
            ],
            'category' => $this->category->name,
            'sub_category' => $this->subCategory->name,
            'images' => $this->images->map(fn($image) => asset('storage/' . $image->path)),
            'video' => $this->video ? asset('storage/' . $this->video) : null,
        ];
    }
}