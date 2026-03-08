<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Messaging\AndroidConfig; // استيراد إعدادات أندرويد
use Kreait\Firebase\Messaging\ApnsConfig;    // استيراد إعدادات آيفون
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function sendNotification(User $recipient, string $title, string $body, array $data = null): void
    {
        Log::info("--- بدء عملية إرسال إشعار فورية للمستخدم ID: {$recipient->id} ---");

        // 1. حفظ في قاعدة البيانات
        try {
            $recipient->notifications()->create([
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);
        } catch (Throwable $dbError) {
            Log::error("فشل حفظ الإشعار داخلياً: " . $dbError->getMessage());
        }

        // 2. جلب التوكينات
        $tokens = $recipient->fcmTokens()->pluck('token')->all();
        if (empty($tokens)) return;

        // 3. بناء إعدادات الأولوية القصوى (High Priority)
        
        // أندرويد: أولوية مرتفعة وصوت افتراضي
        $androidConfig = AndroidConfig::fromArray([
            'priority' => 'high',
            'notification' => [
                'sound' => 'default',
                'color' => '#4f46e5',
            ],
        ]);

        // آيفون: أولوية 10 (فورية) وصوت
        $apnsConfig = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10', // 10 تعني فوراً، 5 تعني لتوفير البطارية
            ],
            'payload' => [
                'aps' => [
                    'sound' => 'default',
                    'content-available' => 1,
                ],
            ],
        ]);

        // 4. بناء الرسالة ودمج الإعدادات
        try {
            $notification = FirebaseNotification::create($title, $body);
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withAndroidConfig($androidConfig) // تفعيل الأولوية لأندرويد
                ->withApnsConfig($apnsConfig);      // تفعيل الأولوية لآيفون
            
            if ($data) {
                $stringData = array_map(function($value) {
                    return is_null($value) ? "" : (string)$value;
                }, $data);
                $message = $message->withData($stringData);
            }

            // 5. الإرسال
            $report = Firebase::messaging()->sendMulticast($message, $tokens);
            Log::info("تم الإرسال بأولوية مرتفعة: " . $report->successes()->count() . " نجاح.");

            if ($report->hasFailures()) {
                $this->handleFailedTokens($report);
            }

        } catch (Throwable $e) {
            Log::error("خطأ FCM: " . $e->getMessage());
        }
    }

    private function handleFailedTokens($report)
    {
        foreach ($report->failures()->getItems() as $failure) {
            $reason = $failure->error()->getMessage();
            $token = $failure->target()->value();
            if (str_contains($reason, 'not a valid') || str_contains($reason, 'not registered')) {
                \App\Models\FcmToken::where('token', $token)->delete();
            }
        }
    }
}