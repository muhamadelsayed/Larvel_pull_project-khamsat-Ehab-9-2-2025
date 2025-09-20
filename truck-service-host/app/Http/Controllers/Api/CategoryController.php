<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Truck;
use App\Http\Resources\UserTruckResource;
/**
 * @group Public Data
 * APIs for retrieving public data like categories.
 */

class CategoryController extends Controller
{

/**
 * @OA\Schema(
 * schema="SubCategory",
 * title="SubCategory",
 * description="A sub-category object",
 * required={"id", "name"},
 * @OA\Property(
 * property="id",
 * type="integer",
 * format="int64",
 * description="The unique ID of the sub-category"
 * ),
 * @OA\Property(
 * property="name",
 * type="string",
 * description="The name of the sub-category"
 * ),
 * @OA\Property(
 * property="category_id",
 * type="integer",
 * format="int64",
 * description="The ID of the parent category"
 * )
 * )
 */
    public function index()
{
    // 1. جلب جميع التصنيفات الرئيسية مع تحميل تصنيفاتها الفرعية
    $categories = Category::with('subCategories')->latest()->get();

    // 2. تحديد أول تصنيف رئيسي (إذا كان موجودًا)
    $firstCategory = $categories->first();
    $initialTrucks = null;

    if ($firstCategory) {
        // 3. إذا كان هناك تصنيفات، جلب أول شاحنات نشطة تنتمي
        // لجميع التصنيفات الفرعية الخاصة بهذا التصنيف الرئيسي
        $subCategoryIds = $firstCategory->subCategories->pluck('id');
        
        $initialTrucks = Truck::whereIn('sub_category_id', $subCategoryIds)
            ->where('status', 'active')
            ->with(['category', 'subCategory', 'images'])
            ->latest()
            ->paginate(10); // paginate الشاحنات
    }

    // 4. بناء الاستجابة النهائية
    return response()->json([
        'categories' => CategoryResource::collection($categories),
        'initial_trucks' => $initialTrucks ? UserTruckResource::collection($initialTrucks) : [
            'data' => [], // إرجاع مصفوفة فارغة إذا لم يكن هناك شاحنات
            // يمكن إضافة معلومات الـ pagination الفارغة هنا أيضًا
        ],
    ]);
}
}