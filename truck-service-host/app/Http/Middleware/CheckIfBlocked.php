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
        $user = null;

        // 1. محاولة التعرف على المستخدم في الـ API (Sanctum)
        if ($request->is('api/*')) {
            $user = Auth::guard('sanctum')->user();
        } 
        
        // 2. إذا لم يكن API، جرب الويب العادي
        if (!$user) {
            $user = $request->user();
        }

        // 3. فحص الحظر إذا وجدنا مستخدماً
        if ($user && $user->blocked_at) {
            
            // رد الـ API
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'تم حظر حسابك، يرجى التواصل مع الإدارة.',
                    'blocked' => true
                ], 403);
            }

            // رد الويب (لوحة التحكم)
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('admin.login')->withErrors([
                'phone' => 'هذا الحساب محظور حالياً.'
            ]);
        }

        return $next($request);
    }
}