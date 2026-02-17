<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckIfBlocked;
use App\Http\Middleware\ForceJsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. إعادة توجيه الضيوف لصفحة الدخول
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        // 2. استثناء الويب هوك من حماية CSRF
        $middleware->validateCsrfTokens(except: [
            'api/payment/webhook',
        ]);

        // 3. إجبار الـ API على إرجاع JSON دائماً
        $middleware->append(ForceJsonResponse::class);

        // 4. تشغيل فحص الحظر على جميع الطلبات (Web & API)
        // وضعه هنا يضمن تشغيله بعد التعرف على المستخدم
        $middleware->append(CheckIfBlocked::class);
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
