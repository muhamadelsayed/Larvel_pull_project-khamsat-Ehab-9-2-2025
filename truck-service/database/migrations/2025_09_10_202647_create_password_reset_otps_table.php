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
    Schema::create('password_reset_otps', function (Blueprint $table) {
        $table->id();
        $table->string('phone')->index(); // index() لسرعة البحث
        $table->string('otp');
        $table->timestamp('expires_at');
        $table->timestamps(); // تضيف created_at و updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};
