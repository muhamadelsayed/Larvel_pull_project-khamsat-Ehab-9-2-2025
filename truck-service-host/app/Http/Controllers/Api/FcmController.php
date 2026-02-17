<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    /**
     * إضافة أو تحديث توكين FCM لجهاز المستخدم.
     */
    public function updateToken(Request $request)
    {
        $validated = $request->validate(['fcm_token' => 'required|string']);

        // firstOrCreate يمنع تكرار نفس التوكين لنفس المستخدم
        $request->user()->fcmTokens()->firstOrCreate([
            'token' => $validated['fcm_token'],
        ]);

        return response()->json(['message' => 'FCM token registered successfully.']);
    }

    /**
     * حذف توكين FCM (عند تسجيل الخروج).
     */
    public function deleteToken(Request $request)
    {
        $validated = $request->validate(['fcm_token' => 'required|string']);

        $request->user()->fcmTokens()->where('token', $validated['fcm_token'])->delete();

        return response()->json(['message' => 'FCM token deleted successfully.']);
    }
}