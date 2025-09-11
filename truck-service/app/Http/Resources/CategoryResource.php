<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'icon' => $this->icon ? Storage::url($this->icon) : null,
        // إضافة التصنيفات الفرعية المرتبطة
        'sub_categories' => SubCategoryResource::collection($this->whenLoaded('subCategories')),
    ];
}

}