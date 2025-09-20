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
    Schema::table('users', function (Blueprint $table) {
        // نضيف العمود بعد حقل 'name' لترتيب الجدول
        $table->string('profile_photo_path', 2048) // 2048 ليكون قادرًا على تخزين روابط طويلة
              ->nullable()
              ->default('https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460__340.png')
              ->after('name');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('profile_photo_path');
    });
}
};
