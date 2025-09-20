<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserTruckResource; // سنعيد استخدام هذا الـ Resource لأنه مناسب
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryTrucksController extends Controller
{
    public function index(SubCategory $subCategory)
    {
        // جلب الشاحنات النشطة فقط لهذا التصنيف الفرعي
        // مع تحميل العلاقات اللازمة لتحسين الأداء
        $trucks = $subCategory->trucks()
            ->where('status', 'active')
            ->with(['category', 'subCategory', 'images'])
            ->latest()
            ->paginate(10); // 10 شاحنات في كل صفحة

        return UserTruckResource::collection($trucks);
    }
}