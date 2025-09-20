<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. تحقق مما إذا كان مسار الطلب يبدأ بـ 'api/'
        if ($request->is('api/*')) {
            // 2. إذا كان كذلك، قم بتعيين ترويسة 'Accept' بالقوة إلى 'application/json'
            $request->headers->set('Accept', 'application/json');
        }

        // 3. مرر الطلب المعدل إلى الخطوة التالية في الـ pipeline
        return $next($request);
    }
}