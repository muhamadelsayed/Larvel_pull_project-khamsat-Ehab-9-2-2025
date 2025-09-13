<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserTruckResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'pickup_location' => $this->pickup_location,
            'price_per_day' => $this->price_per_day,
            'category' => $this->category->name,
            'sub_category' => $this->subCategory->name,
            // جلب رابط الصورة الأولى فقط (إذا وجدت)
            'main_image' => $this->images->first() ? asset('storage/' . $this->images->first()->path) : null,
        ];
    }
}