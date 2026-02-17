<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->blocked_at) {
            
            // إذا كان الطلب API
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account is blocked. Please contact support.',
                    'blocked' => true
                ], 403);
            }

            // إذا كان طلب ويب (لوحة التحكم مثلاً)
            Auth::logout();
            return redirect()->route('admin.login')->withErrors(['phone' => 'هذا الحساب محظور حالياً.']);
        }

        return $next($request);
    }
}
