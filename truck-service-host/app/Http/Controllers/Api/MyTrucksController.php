<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MyTruckResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group My Trucks
 * APIs for a truck owner to view their own trucks.
 */

class MyTrucksController extends Controller
{
    /**
     * @OA\PathItem(
     *      path="/api/my-trucks",
     *      @OA\Get(
     *          operationId="getMyTrucks",
     *          tags={"My Trucks"},
     *          summary="Get the authenticated user's trucks",
     *          description="Returns a paginated list of the user's own trucks, with filtering options.",
     *          security={{"bearerAuth":{}}},
     *          @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending", "active", "inactive"})),
     *          @OA\Parameter(name="sort_by", in="query", @OA\Schema(type="string", enum={"latest", "oldest"})),
     *          @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *          @OA\Response(response=200, description="Successful operation"),
     *          @OA\Response(response=401, description="Unauthenticated"),
     *      )
     * )
     */
    public function index(Request $request)
    {
        // 1. التحقق من صحة الفلاتر المرسلة (اختياري)
        $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'active', 'inactive'])],
            'sort_by' => ['nullable', Rule::in(['latest', 'oldest'])],
        ]);

        // 2. البدء ببناء الاستعلام لشاحنات المستخدم الحالي فقط
        $trucksQuery = auth()->user()->trucks()
            ->with(['category', 'images']); // تحميل العلاقات لتحسين الأداء

        // 3. تطبيق فلتر الحالة (status) إذا تم إرساله
        if ($request->filled('status')) {
            $trucksQuery->where('status', $request->status);
        }

        // 4. تطبيق الترتيب
        $sortBy = $request->input('sort_by', 'latest'); // الافتراضي هو الأحدث
        if ($sortBy === 'latest') {
            $trucksQuery->latest(); // يرتب تنازلياً حسب created_at
        } else {
            $trucksQuery->oldest(); // يرتب تصاعدياً حسب created_at
        }
        
        // 5. تنفيذ الاستعلام مع pagination
        // paginate(10) يعني عرض 10 شاحنات في كل صفحة
        $paginatedTrucks = $trucksQuery->paginate(10);
        
        // 6. إرجاع النتائج باستخدام الـ Resource
        return MyTruckResource::collection($paginatedTrucks);
    }
}