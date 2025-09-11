<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


// app/Http/Resources/TruckResource.php
class TruckResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'model' => $this->model,
            'year_of_manufacture' => $this->year_of_manufacture,
            'description' => $this->description,
            'price_per_day' => $this->price_per_day,
            'price_per_hour' => $this->price_per_hour,
            'work_hours' => $this->work_start_time . ' - ' . $this->work_end_time,
            'pickup_location' => $this->pickup_location,
            'delivery_price' => $this->delivery_price,
            // جلب بيانات صاحب الشاحنة
            'owner' => [
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ],
            'category' => $this->category->name,
            'sub_category' => $this->subCategory->name,
            // جلب روابط الصور والفيديو الكاملة
            'images' => $this->images->map(fn($image) => Storage::url($image->path)),
            'video' => $this->video ? Storage::url($this->video) : null,
        ];
    }
}