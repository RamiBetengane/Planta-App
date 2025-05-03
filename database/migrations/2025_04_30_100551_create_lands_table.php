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
        Schema::create('lands', function (Blueprint $table) {
            $table->id();
            $table->string('location_name');
            $table->decimal('latitude', 10, 7);    // لتحديد إحداثيات دقيقة
            $table->decimal('longitude', 10, 7);
            $table->decimal('total_area', 10, 2);  // يمكن تغيير الحجم والدقة حسب الحاجة
            $table->enum('land_type', ['private', 'government', 'unused']);
            $table->string('soil_type');
            $table->enum('status', ['available' , 'reserved', 'planted' , 'inactive']);
            $table->text('description');
            $table->string('water_source');
            $table->foreignId('owner_id')->constrained()->onDelete('cascade');
            $table->string('image')->nullable();  // إضافة الحقل للصورة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lands');
    }
};
