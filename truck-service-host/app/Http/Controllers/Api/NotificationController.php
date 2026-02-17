<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * جلب قائمة الإشعارات الخاصة بالمستخدم المسجل دخوله.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications() // استخدام العلاقة التي أنشأناها
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }
}