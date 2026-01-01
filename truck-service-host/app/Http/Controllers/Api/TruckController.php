<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\TruckResource;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\UserTruckResource;

/**
 * @group Truck Management
 * APIs for truck owners to manage their trucks.
 */

class TruckController extends Controller
{
    use AuthorizesRequests;
    /**
     * عرض قائمة بجميع الشاحنات النشطة مع دعم الفلاتر والـ pagination.
     */
    public function index(Request $request)
    {
        // 1. التحقق من صحة الفلاتر (اختياري)
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'sub_category_id' => 'nullable|integer|exists:sub_categories,id',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|string|in:latest,price_asc,price_desc'
        ]);

        // 2. البدء بالاستعلام الأساسي: جلب الشاحنات النشطة فقط
        $trucksQuery = Truck::query()->where('status', 'active');

        // 3. تطبيق الفلاتر الاختيارية
        
        // فلتر حسب التصنيف الرئيسي
        $trucksQuery->when($request->category_id, function ($query, $categoryId) {
            // هنا نستخدم علاقة hasManyThrough التي أنشأناها
            $query->whereHas('category', fn($q) => $q->where('id', $categoryId));
        });
        
        // فلتر حسب التصنيف الفرعي
        $trucksQuery->when($request->sub_category_id, function ($query, $subCategoryId) {
            $query->where('sub_category_id', $subCategoryId);
        });

        // فلتر حسب نطاق السعر (لليوم)
        $trucksQuery->when($request->price_min, function ($query, $priceMin) {
            $query->where('price_per_day', '>=', $priceMin);
        });

        $trucksQuery->when($request->price_max, function ($query, $priceMax) {
            $query->where('price_per_day', '<=', $priceMax);
        });
        
        // 4. تطبيق الترتيب
        $sortBy = $request->input('sort_by', 'latest'); // الافتراضي هو الأحدث
        
        if ($sortBy === 'price_asc') {
            $trucksQuery->orderBy('price_per_day', 'asc');
        } elseif ($sortBy === 'price_desc') {
            $trucksQuery->orderBy('price_per_day', 'desc');
        } else {
            $trucksQuery->latest(); // Default sort
        }

        // 5. تحميل العلاقات اللازمة (Eager Loading) لتجنب استعلامات N+1
        $trucks = $trucksQuery->with(['category', 'subCategory', 'images'])->paginate(12); // 12 شاحنة في الصفحة

        // 6. إرجاع النتائج باستخدام الـ Resource
        return UserTruckResource::collection($trucks);
    }
   
    public function show(Truck $truck)
{
    // 1. التحقق مما إذا كان هناك مستخدم مسجل دخوله
    $user = auth('sanctum')->user();

    // 2. تحديد ما إذا كان المستخدم الحالي هو مالك الشاحنة
    $isOwner = $user && $user->id === $truck->user_id;

    // 3. تطبيق منطق الصلاحية
    // اسمح بالوصول إذا كانت الشاحنة "نشطة" (لأي شخص)
    // أو إذا كان المستخدم الحالي هو "المالك" (لأي حالة)
    if ($truck->status === 'active' || $isOwner) {
        // إذا كان الوصول مسموحًا، قم بتحميل كل العلاقات وأرجع البيانات
        return new TruckResource($truck->load('user', 'category', 'subCategory', 'images'));
    }

    // 4. إذا لم تتحقق الشروط، أرجع خطأ "غير موجود"
    return response()->json(['message' => 'Truck not found or you do not have permission to view it.'], 404);
}


public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
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

 
public function update(Request $request, Truck $truck)
{
    // 1. التحقق من أن المستخدم يملك الشاحنة
    $this->authorize('update', $truck);

    // 2. التحقق من صحة المدخلات (جميعها اختيارية)
    // "sometimes" تعني: قم بتطبيق قواعد التحقق هذه فقط إذا كان الحقل موجودًا في الطلب.
    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
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
        
        // 3.1 فصل البيانات النصية عن الوسائط
        $truckData = collect($validated)->except(['images', 'video'])->all();
        
        // 3.2 تحديث البيانات النصية وإعادة الحالة إلى "قيد المراجعة"
        // فقط إذا تم إرسال أي بيانات نصية
        if (!empty($truckData)) {
            $truck->update(array_merge($truckData, ['status' => 'pending']));
        }

        // 3.3 التعامل مع تحديث الفيديو (إذا تم إرسال ملف جديد)
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('trucks/videos', 'public');
            // التأكد من أن الحالة تصبح pending حتى لو تم تحديث الفيديو فقط
            $truck->update(['video' => $videoPath, 'status' => 'pending']);
        }

        // 3.4 التعامل مع تحديث الصور (إذا تم إرسال ملفات جديدة)
        if ($request->hasFile('images')) {
            // حذف سجلات الصور القديمة من قاعدة البيانات
            $truck->images()->delete();
            
            // إضافة سجلات الصور الجديدة
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('trucks/images', 'public');
                $truck->images()->create(['path' => $path]);
            }
            // التأكد من أن الحالة تصبح pending حتى لو تم تحديث الصور فقط
            $truck->update(['status' => 'pending']);
        }
    });

    return response()->json([
        'message' => 'Truck updated and awaiting re-approval.',
        'truck_id' => $truck->id
    ], 200);
}

 
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