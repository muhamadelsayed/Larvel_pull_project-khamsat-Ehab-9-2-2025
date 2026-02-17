<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        // حقن الـ Service ليكون متاحًا في كل الدوال
        $this->notificationService = $notificationService;
    }

    // -------------------------------------------------------------
    // 1. الإرسال لجميع المستخدمين (Admin/Manager Action)
    // -------------------------------------------------------------
    public function showBroadcastForm()
    {
        return view('admin.notifications.broadcast');
    }

    public function sendBroadcast(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
        ]);

        // جلب جميع المستخدمين الذين لديهم توكين FCM مسجل
        $recipients = User::whereHas('fcmTokens')->get();
        
        $sentCount = 0;
        foreach ($recipients as $user) {
            $this->notificationService->sendNotification(
                $user,
                $validated['title'],
                $validated['body'],
                ['type' => 'broadcast']
            );
            $sentCount++;
        }

        return redirect()->route('admin.notifications.broadcast.form')->with('success', "تم إرسال الإشعار لـ ({$sentCount}) مستخدم بنجاح.");
    }

    // -------------------------------------------------------------
    // 2. الإرسال لمستخدم واحد (Admin/Manager Action)
    // -------------------------------------------------------------
    public function sendToUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
        ]);
        
        // التأكد من أن المستخدم لديه توكين قبل الإرسال
        if ($user->fcmTokens->isEmpty()) {
            return back()->with('error', 'هذا المستخدم ليس لديه جهاز مسجل لإرسال الإشعار.');
        }

        $this->notificationService->sendNotification(
            $user,
            $validated['title'],
            $validated['body'],
            ['type' => 'custom', 'user_id' => (string)$user->id]
        );

        return back()->with('success', "تم إرسال الإشعار للمستخدم {$user->name} بنجاح.");
    }
}