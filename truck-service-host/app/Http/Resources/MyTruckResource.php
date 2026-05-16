<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyTruckResource extends JsonResource
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
            'model' => $this->model,
            'status' => $this->status, // <-- أهم حقل هنا
            'price_per_day' => $this->price_per_day,
            'name' => $this->name,
            'category' => $this->category->name,
            'pickup_location' => $this->pickup_location,
            'delivery_available' => (bool) $this->delivery_available,
            'delivery_price' => $this->delivery_price,
            'description' => $this->description,
            'additional_features' => $this->additional_features,
            'sub_category' => $this->subCategory->name,
            // عرض الصورة الرئيسية للشاحنة
            'main_image' => $this->images->first() ? asset('storage/' . $this->images->first()->path) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}