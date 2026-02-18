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
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        $middleware->validateCsrfTokens(except: [
            'api/payment/webhook',
        ]);

        // إجبار JSON
        $middleware->append(ForceJsonResponse::class);

        // جعل ميدل وير الحظر يعمل بعد تعريف الجلسات والتوكينات
        $middleware->alias([
            'check.blocked' => CheckIfBlocked::class,
        ]);

        // إضافته للمجموعات لضمان العمل على المستخدمين المسجلين
        $middleware->appendToGroup('api', 'check.blocked');
        $middleware->appendToGroup('web', 'check.blocked');
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();