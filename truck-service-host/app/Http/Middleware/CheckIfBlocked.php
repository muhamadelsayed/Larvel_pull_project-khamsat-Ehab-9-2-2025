<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckIfBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق مما إذا كان المستخدم مسجلاً ومحظوراً
        $user = $request->user();

        if ($user && $user->blocked_at) {
            
            // حالة الـ API (بوست مان / فلاتر)
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'تم حظر حسابك، يرجى التواصل مع الإدارة.',
                    'blocked' => true
                ], 403);
            }

            // حالة الويب (لوحة التحكم)
            Auth::guard('web')->logout();
            
            // تنظيف الجلسة يدوياً لضمان الخروج التام
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('admin.login')->withErrors([
                'phone' => 'هذا الحساب محظور حالياً، يرجى مراجعة الإدارة.'
            ]);
        }

        return $next($request);
    }
}