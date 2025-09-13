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
    Schema::create('trucks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // صاحب الشاحنة
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->foreignId('sub_category_id')->constrained()->onDelete('cascade');

        $table->year('year_of_manufacture');
        $table->string('size'); // e.g., "20 Ton", "Large"
        $table->string('model');
        $table->text('description');
        $table->text('additional_features')->nullable();

        $table->string('video')->nullable();
        
        $table->decimal('price_per_day', 8, 2);
        $table->decimal('price_per_hour', 8, 2);

        $table->time('work_start_time');
        $table->time('work_end_time');

        $table->text('pickup_location');
        $table->boolean('delivery_available')->default(false);
        $table->decimal('delivery_price', 8, 2)->nullable();

        $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};
