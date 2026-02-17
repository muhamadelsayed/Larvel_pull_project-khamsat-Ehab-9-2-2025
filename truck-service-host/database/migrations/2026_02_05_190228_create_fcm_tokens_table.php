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
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();

            // 1. تحديد نوع العمود بشكل صريح ليكون متطابقًا مع users.id
            $table->unsignedBigInteger('user_id');
            
            // 2. تغيير نوع التوكين إلى string للسماح بإنشاء فهرس unique
            $table->string('token'); 
            
            $table->timestamps();

            // 3. إنشاء المفتاح الأجنبي
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // 4. إنشاء الفهرس الفريد
            $table->unique(['user_id', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};