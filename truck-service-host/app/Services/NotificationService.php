<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Laravel\Firebase\Facades\Firebase; // استخدام الواجهة (Facade)
use Throwable;

class NotificationService
{
    /**
     * إرسال إشعار إلى مستخدم معين (لجميع أجهزته).
     *
     * @param User $recipient المستقبِل
     * @param string $title عنوان الإشعار
     * @param string $body نص الإشعار
     * @param array|null $data بيانات إضافية (مثل booking_id)
     */
    public function sendNotification(User $recipient, string $title, string $body, array $data = null): void
    {
        // 1. جلب جميع توكينات FCM النشطة للمستخدم
        $tokens = $recipient->fcmTokens()->pluck('token')->all();

        // 2. لا تفعل شيئًا إذا لم يكن لدى المستخدم أي توكينات مسجلة
        if (empty($tokens)) {
            return;
        }

        // 3. حفظ الإشعار في قاعدة البيانات لدينا كسجل دائم
        $recipient->notifications()->create([
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        // 4. بناء رسالة Firebase
        $notification = FirebaseNotification::create($title, $body);
        
        // 5. إرسال الإشعار إلى جميع الأجهزة
        // sendMulticast هو الأفضل لإرسال نفس الرسالة لعدة توكينات
        $message = CloudMessage::new()->withNotification($notification);
        if ($data) {
            // تحويل كل قيم البيانات إلى نص (متطلب من FCM)
            $stringData = array_map('strval', $data);
            $message = $message->withData($stringData);
        }

        try {
            $report = Firebase::messaging()->sendMulticast($message, $tokens);
            
            // (اختياري) التعامل مع التوكينات غير الصالحة
            if ($report->hasFailures()) {
                $this->handleFailedTokens($report, $tokens);
            }
        } catch (Throwable $e) {
            // (اختياري) سجل أي أخطاء فادحة تحدث أثناء الإرسال
            \Log::error('FCM Multicast Send Error: ' . $e->getMessage());
        }
    }

    /**
     * (اختياري) دالة لتنظيف التوكينات غير الصالحة من قاعدة البيانات.
     */
    private function handleFailedTokens($report, $tokens)
    {
        $failedTokens = $report->invalidTokens();

        if (!empty($failedTokens)) {
            \App\Models\FcmToken::whereIn('token', $failedTokens)->delete();
            \Log::info('Deleted invalid FCM tokens: ' . implode(', ', $failedTokens));
        }
    }
}