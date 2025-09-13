<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\TruckResource;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @group Truck Management
 * APIs for truck owners to manage their trucks.
 */

class TruckController extends Controller
{
    use AuthorizesRequests;
    /**
     * @OA\PathItem(
     *      path="/api/trucks/{truck}",
     *      @OA\Get(
     *          operationId="getTruckDetails",
     *          tags={"Public Data"},
     *          summary="Get details of a single truck",
     *          @OA\Parameter(name="truck", in="path", required=true, @OA\Schema(type="integer")),
    *          @OA\Response(
    *              response=200,
    *              description="Truck details retrieved successfully",
    *              @OA\JsonContent(
    *                  @OA\Property(property="data", type="object", ref="#/components/schemas/TruckResource")
    *              )
    *          ),
    *          @OA\Response(
    *              response=404,
    *              description="Not found or not active.",
    *              @OA\JsonContent(
    *                  @OA\Property(property="message", type="string", example="Not found or not active.")
    *              )
    *          )
    *      )
    * )
     */
    public function show(Truck $truck)
    {
        // تأكد من أن الشاحنة نشطة قبل عرضها للجميع
        if ($truck->status !== 'active' && $truck->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not found or not active.'], 404);
        }
        return new TruckResource($truck->load('user', 'category', 'subCategory', 'images'));
    }

/**
 * @OA\PathItem(
 *      path="/api/trucks",
 *      @OA\Post(
 *          operationId="storeTruck",
 *          tags={"Truck Management"},
 *          summary="Add a new truck",
 *          description="Requires authentication. Submits a new truck for admin approval.",
 *          security={{"bearerAuth":{}}},
 *          @OA\RequestBody(
 *              required=true,
 *              @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
 *                  required={"category_id", "sub_category_id", "year_of_manufacture", "size", "model", "description", "price_per_day", "price_per_hour", "work_start_time", "work_end_time", "pickup_location", "delivery_available"},
 *                  @OA\Property(property="category_id", type="integer"),
 *                  @OA\Property(property="sub_category_id", type="integer"),
 *                  @OA\Property(property="year_of_manufacture", type="integer", example="2023"),
 *                  @OA\Property(property="size", type="string", example="25 Ton"),
 *                  @OA\Property(property="model", type="string", example="Caterpillar 320D"),
 *                  @OA\Property(property="description", type="string"),
 *                  @OA\Property(property="price_per_day", type="number", format="float"),
 *                  @OA\Property(property="price_per_hour", type="number", format="float"),
 *                  @OA\Property(property="work_start_time", type="string", format="time", example="08:00"),
 *                  @OA\Property(property="work_end_time", type="string", format="time", example="18:00"),
 *                  @OA\Property(property="pickup_location", type="string"),
 *                  @OA\Property(property="delivery_available", type="boolean"),
 *                  @OA\Property(property="delivery_price", type="number", format="float"),
 *                  @OA\Property(property="images[]", type="string", format="binary"),
 *                  @OA\Property(property="video", type="string", format="binary"),
 *              ))
 *          ),
 *          @OA\Response(
 *              response=201,
 *              description="Truck submitted for approval.",
 *              @OA\JsonContent(
 *                  @OA\Property(property="message", type="string", example="Truck submitted for approval."),
 *                  @OA\Property(property="truck_id", type="integer", example=123)
 *              )
 *          ),
 *          @OA\Response(
 *              response=422,
 *              description="Validation error",
 *              @OA\JsonContent(
 *                  @OA\Property(property="message", type="string", example="The given data was invalid."),
 *                  @OA\Property(property="errors", type="object")
 *              )
 *          ),
 *          @OA\Response(
 *              response=401,
 *              description="Unauthenticated",
 *              @OA\JsonContent(
 *                  @OA\Property(property="message", type="string", example="Unauthenticated.")
 *              )
 *          )
 *      )
 * )
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'sub_category_id' => 'required|exists:sub_categories,id',
        'year_of_manufacture' => 'required|digits:4',
        'size' => 'required|string',
        'model' => 'required|string',
        'description' => 'required|string',
        'additional_features' => 'nullable|string',
        'images' => 'nullable|array|max:3',
        'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        'video' => 'nullable|mimes:mp4,mov,avi|max:10240', // 10MB
        'price_per_day' => 'required|numeric',
        'price_per_hour' => 'required|numeric',
        'work_start_time' => 'required|date_format:H:i',
        'work_end_time' => 'required|date_format:H:i',
        'pickup_location' => 'required|string',
        'delivery_available' => 'required|boolean',
        'delivery_price' => 'nullable|numeric|required_if:delivery_available,true',
    ]);

    $truck = DB::transaction(function () use ($request, $validated) {
        
        // الخطوة 1: استبعاد مفاتيح الوسائط من المصفوفة الرئيسية
        $truckData = collect($validated)->except(['images', 'video'])->all();
        
        // الخطوة 2: إنشاء الشاحنة بالبيانات النصية فقط
        $truck = auth()->user()->trucks()->create($truckData);

        // الخطوة 3: التعامل مع الصور (إذا كانت موجودة)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('trucks/images', 'public');
                // إنشاء سجل في جدول `truck_images` وربطه بالشاحنة
                $truck->images()->create(['path' => $path]);
            }
        }
        
        // الخطوة 4: التعامل مع الفيديو (إذا كان موجودًا)
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('trucks/videos', 'public');
            // تحديث سجل الشاحنة بمسار الفيديو
            $truck->update(['video' => $videoPath]);
        }
        
        return $truck;
    });

    return response()->json(['message' => 'Truck submitted for approval.', 'truck_id' => $truck->id], 201);
}

 /**
     * @OA\PathItem(
     *      path="/api/trucks/{truck}",
     *      @OA\Post(
     *          operationId="updateTruck",
     *          tags={"Truck Management"},
     *          summary="Update an existing truck",
     *          description="To update a truck with files (images/video), you must send a POST request and include `_method=PATCH` in the form-data. This is a limitation of how browsers handle file uploads.",
     *          security={{"bearerAuth":{}}},
     *          @OA\Parameter(name="truck", in="path", required=true, @OA\Schema(type="integer")),
     *          @OA\RequestBody(
     *              required=true,
     *              @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
     *                  @OA\Property(property="_method", type="string", enum={"PATCH"}, example="PATCH"),
     *                  @OA\Property(property="price_per_day", type="number", format="float", example="1600.50"),
     *                  @OA\Property(property="images[]", type="string", format="binary")
     *              ))
     *          ),
    *          @OA\Response(
    *              response=200,
    *              description="Truck updated and awaiting re-approval.",
    *              @OA\JsonContent(
    *                  @OA\Property(property="message", type="string", example="Truck updated and awaiting re-approval."),
    *                  @OA\Property(property="truck_id", type="integer", example=123)
    *              )
    *          ),
    *          @OA\Response(
    *              response=422,
    *              description="Validation error",
    *              @OA\JsonContent(
    *                  @OA\Property(property="message", type="string", example="The given data was invalid."),
    *                  @OA\Property(property="errors", type="object")
    *              )
    *          ),
    *          @OA\Response(
    *              response=401,
    *              description="Unauthenticated",
    *              @OA\JsonContent(
    *                  @OA\Property(property="message", type="string", example="Unauthenticated.")
    *              )
    *          ),
    *          @OA\Response(
    *              response=403,
    *              description="Forbidden",
    *              @OA\JsonContent(
    *                  @OA\Property(property="message", type="string", example="This action is unauthorized.")
    *              )
    *          ),
    *          @OA\Response(
    *              response=404,
    *              description="Truck not found",
    *              @OA\JsonContent(
    *                  @OA\Property(property="message", type="string", example="Truck not found.")
    *              )
    *          )
     *      )
     * )
     */
public function update(Request $request, Truck $truck)
{
    // 1. التحقق من أن المستخدم يملك الشاحنة
    $this->authorize('update', $truck);

    // 2. التحقق من صحة المدخلات
    $validated = $request->validate([
        'category_id' => 'sometimes|required|exists:categories,id',
        'sub_category_id' => 'sometimes|required|exists:sub_categories,id',
        'year_of_manufacture' => 'sometimes|required|digits:4',
        'size' => 'sometimes|required|string',
        'model' => 'sometimes|required|string',
        'description' => 'sometimes|required|string',
        'additional_features' => 'nullable|string',
        'price_per_day' => 'sometimes|required|numeric',
        'price_per_hour' => 'sometimes|required|numeric',
        'work_start_time' => 'sometimes|required|date_format:H:i',
        'work_end_time' => 'sometimes|required|date_format:H:i',
        'pickup_location' => 'sometimes|required|string',
        'delivery_available' => 'sometimes|required|boolean',
        'delivery_price' => 'nullable|numeric|required_if:delivery_available,true',
        
        'images' => 'nullable|array|max:3',
        'images.*' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
    ]);

    // 3. تحديث البيانات داخل Transaction
    DB::transaction(function () use ($request, $truck, $validated) {
        
        // الخطوة 3.1: فصل البيانات النصية عن الوسائط
        $truckData = collect($validated)->except(['images', 'video'])->all();

        // الخطوة 3.2: تحديث البيانات النصية وإعادة الحالة إلى "قيد المراجعة"
        $truck->update(array_merge($truckData, ['status' => 'pending']));

        // الخطوة 3.3: التعامل مع تحديث الفيديو (إذا تم إرسال ملف جديد)
        if ($request->hasFile('video')) {
            // ملاحظة: لا نحذف الفيديو القديم بناءً على طلبك
            $videoPath = $request->file('video')->store('trucks/videos', 'public');
            $truck->update(['video' => $videoPath]);
        }

        // الخطوة 3.4: التعامل مع تحديث الصور (إذا تم إرسال ملفات جديدة)
        if ($request->hasFile('images')) {
            // أولاً، نحذف سجلات الصور القديمة من قاعدة البيانات
            // (الملفات الفعلية لا تُحذف)
            $truck->images()->delete();
            
            // ثم نضيف سجلات الصور الجديدة
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('trucks/images', 'public');
                $truck->images()->create(['path' => $path]);
            }
        }
    });

    return response()->json([
        'message' => 'Truck updated and awaiting re-approval.',
        'truck_id' => $truck->id
    ], 200);
}

   /**
     * @OA\Delete(
     *      path="/api/trucks/{truck}",
     *      operationId="deleteTruck",
     *      tags={"Truck Management"},
     *      summary="Delete a truck",
     *      description="Requires authentication. Allows a truck owner to delete their own truck.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(name="truck", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="Truck has been successfully deleted.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Truck has been successfully deleted.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Truck not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Truck not found.")
     *          )
     *      )
     * )
     */
public function destroy(Truck $truck)
{
    // 1. التحقق من أن المستخدم يملك الشاحنة
    $this->authorize('delete', $truck);

    // 2. حذف الشاحنة من قاعدة البيانات
    // سيتم حذف الصور المرتبطة تلقائيًا بسبب onDelete('cascade')
    $truck->delete();
    // ملاحظة: لا نحذف الملفات من storage بناءً على طلبك

    return response()->json(['message' => 'Truck has been successfully deleted.'], 200);
}
}