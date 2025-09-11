<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\TruckResource;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TruckController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    // عرض تفاصيل شاحنة واحدة
    public function show(Truck $truck)
    {
        // تأكد من أن الشاحنة نشطة قبل عرضها للجميع
        if ($truck->status !== 'active' && $truck->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not found or not active.'], 404);
        }
        return new TruckResource($truck->load('user', 'category', 'subCategory', 'images'));
    }

    // إضافة شاحنة جديدة
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

    // تحديث شاحنة موجودة
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
        
        // قواعد التحقق للوسائط (اختيارية في التحديث)
        'images' => 'nullable|array|max:3',
        'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        'video' => 'nullable|mimes:mp4,mov,avi|max:10240', // 10MB
    ]);

    // 3. تحديث البيانات داخل Transaction
    DB::transaction(function () use ($request, $truck, $validated) {
        // تحديث البيانات النصية وإعادة الحالة إلى "قيد المراجعة"
        $truck->update(array_merge($validated, ['status' => 'pending']));

        // 4. منطق تحديث الفيديو (استبدال المسار فقط)
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('trucks/videos', 'public');
            $truck->update(['video' => $videoPath]);
            // ملاحظة: لا نحذف الفيديو القديم بناءً على طلبك
        }

        // 5. منطق تحديث الصور (إضافة/استبدال)
        if ($request->hasFile('images')) {
            // أولاً، نحذف سجلات الصور القديمة من قاعدة البيانات
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

    // app/Http/Controllers/Api/TruckController.php
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