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
    Schema::table('password_reset_otps', function (Blueprint $table) {
        // نضيف حقل من نوع timestamp، ونسمح بأن يكون null
        // ونضعه بعد حقل expires_at لترتيب الجدول
        $table->timestamp('used_at')->nullable()->after('expires_at');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('password_reset_otps', function (Blueprint $table) {
        // هذا الكود للتراجع عن التغيير إذا احتجنا ذلك
        $table->dropColumn('used_at');
    });
}
};
