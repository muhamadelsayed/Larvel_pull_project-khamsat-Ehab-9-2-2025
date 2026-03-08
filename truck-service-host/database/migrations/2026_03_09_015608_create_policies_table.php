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
    Schema::create('policies', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // عنوان البند (مثلاً: سياسة الإلغاء)
        $table->text('content'); // الوصف التفصيلي
        $table->integer('sort_order')->default(0); // لترتيب البنود
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
