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
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('truck_id')->constrained()->onDelete('cascade'); // الشاحنة المحجوزة
        $table->foreignId('customer_id')->constrained('users')->onDelete('cascade'); // العميل الذي قام بالحجز
        
        $table->dateTime('start_datetime'); // تاريخ ووقت بدء الحجز
        $table->dateTime('end_datetime');   // تاريخ ووقت انتهاء الحجز

        $table->decimal('base_price', 10, 2); // سعر الإيجار الأساسي (أيام + ساعات)
        $table->decimal('delivery_price', 10, 2)->nullable(); // سعر التوصيل (إذا طُلب)
        $table->decimal('total_price', 10, 2); // السعر الإجمالي

        // دورة حياة الحجز
        $table->enum('status', [
            'pending',          // بانتظار موافقة صاحب الشاحنة
            'approved',         // تمت الموافقة، بانتظار الدفع
            'confirmed',        // تم الدفع، الحجز مؤكد
            'rejected',         // مرفوض من صاحب الشاحنة
            'cancelled',        // ملغي من قبل العميل
            'completed',        // اكتمل الحجز
        ])->default('pending');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
