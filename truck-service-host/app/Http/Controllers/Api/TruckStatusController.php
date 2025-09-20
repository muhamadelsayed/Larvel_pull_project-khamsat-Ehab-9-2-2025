<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TruckStatusController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * إلغاء تفعيل شاحنة من قبل مالكها.
     * يمكن فقط إلغاء تفعيل الشاحنات التي حالتها 'active'.
     */
    public function deactivate(Truck $truck): JsonResponse
    {
        // 1. التحقق من أن المستخدم يملك الشاحنة (باستخدام Policy)
        $this->authorize('update', $truck);

        // 2. التحقق من أن الشاحنة نشطة بالفعل
        if ($truck->status !== 'active') {
            return response()->json([
                'message' => 'Only active trucks can be deactivated.'
            ], 409); // 409 Conflict - الحالة الحالية لا تسمح بهذا الإجراء
        }

        // 3. تحديث الحالة
        $truck->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Truck has been successfully deactivated.',
            'status' => 'inactive'
        ]);
    }

    /**
     * طلب إعادة تفعيل شاحنة من قبل مالكها.
     * يمكن فقط طلب تفعيل الشاحنات التي حالتها 'inactive'.
     */
    public function requestActivation(Truck $truck): JsonResponse
    {
        // 1. التحقق من أن المستخدم يملك الشاحنة (باستخدام Policy)
        $this->authorize('update', $truck);

        // 2. التحقق من أن الشاحنة غير نشطة
        if ($truck->status !== 'inactive') {
            return response()->json([
                'message' => 'Activation can only be requested for inactive trucks.'
            ], 409); // 409 Conflict
        }

        // 3. تحديث الحالة إلى "قيد المراجعة"
        $truck->update(['status' => 'pending']);
        
        // (مستقبلاً، يمكن إرسال إشعار للإدارة هنا)

        return response()->json([
            'message' => 'Activation request sent successfully. Awaiting admin approval.',
            'status' => 'pending'
        ]);
    }
}