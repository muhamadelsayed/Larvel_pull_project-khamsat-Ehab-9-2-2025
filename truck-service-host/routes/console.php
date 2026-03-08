<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// تشغيل أمر الإكمال كل 10 دقائق
// withoutOverlapping تضمن عدم تداخل العمليات إذا استغرقت إحداها وقتاً أطول
Schedule::command('bookings:complete')->everyTenMinutes()->withoutOverlapping();
