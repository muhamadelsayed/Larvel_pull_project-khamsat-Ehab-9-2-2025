<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        // لتخزين الـ ID الذي ترجعه Tap عند إنشاء العملية
        $table->string('tap_charge_id')->nullable()->after('status');
        // لتخزين حالة الدفع بشكل مفصل (اختياري للتدقيق)
        $table->string('payment_method')->nullable()->after('tap_charge_id');
    });
}

public function down(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->dropColumn(['tap_charge_id', 'payment_method']);
    });
}
};
