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
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->text('scientific_name');
            $table->text('common_name');
            $table->text('description');
            $table->text('water_requirements');
            $table->text('sun_requirements');
            $table->text('suitable_soil_types');
            $table->text('co2_absorption');
            $table->text('cancer_risk_impact');
            $table->integer('growth_min_months');
            $table->integer('growth_max_months');
            $table->string('image')->nullable();  // إضافة الحقل للصورة
            $table->float('required_area');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
