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
    *          @OA\Response(
    *              response=200,
    *              description="Successful operation",
    *              @OA\JsonContent(
    *                  type="object",
    *                  @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MyTruckResource")),
    *                  @OA\Property(property="links", type="object"),
    *                  @OA\Property(property="meta", type="object")
    *              )
    *          ),
    *          @OA\Response(
    *              response=401,
    *              description="Unauthenticated",
    *              @OA\JsonContent(
    *                  type="object",
    *                  @OA\Property(property="message", type="string", example="Unauthenticated.")
    *              )
    *          ),
    *          @OA\Response(
    *              response=422,
    *              description="Validation Error",
    *              @OA\JsonContent(
    *                  type="object",
    *                  @OA\Property(property="message", type="string", example="The given data was invalid."),
    *                  @OA\Property(property="errors", type="object")
    *              )
    *          )
    *      )
    * )
        */
     public function index(Request $request)
    {
        // 1. التحقق من صحة الفلاتر المرسلة
        $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'active', 'inactive'])],
            'sub_category_id' => 'nullable|integer|exists:sub_categories,id', // <-- إضافة جديدة
            'sort_by' => ['nullable', Rule::in(['latest', 'oldest'])],
        ]);

        // 2. البدء ببناء الاستعلام لشاحنات المستخدم الحالي فقط
        $trucksQuery = auth()->user()->trucks()
            ->with(['category','subCategory', 'images']); // تحميل العلاقات لتحسين الأداء

        // 3. تطبيق فلتر الحالة (status) إذا تم إرساله
        $trucksQuery->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        });

        // 4. تطبيق فلتر التصنيف الفرعي (sub_category_id) إذا تم إرساله <-- إضافة جديدة
        $trucksQuery->when($request->filled('sub_category_id'), function ($query) use ($request) {
            $query->where('sub_category_id', $request->sub_category_id);
        });

        // 5. تطبيق الترتيب
        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'latest') {
            $trucksQuery->latest();
        } else {
            $trucksQuery->oldest();
        }
        
        // 6. تنفيذ الاستعلام مع pagination
        $paginatedTrucks = $trucksQuery->paginate(10);
        
        // 7. إرجاع النتائج باستخدام الـ Resource
        return MyTruckResource::collection($paginatedTrucks);
    }
}