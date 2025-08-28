<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_details', function (Blueprint $table) {
            $table->id();

            // ربط مع offer (واحد offer له عدة offer_details)
            $table->foreignId('offer_id')
                ->constrained('offers')
                ->onDelete('cascade');

            // ربط مع plant_request (واحد plant_request له عدة offer_details)
            $table->foreignId('plant_request_id')
                ->constrained('plant_request')
                ->onDelete('cascade');

            $table->foreignId('plant_id')
                ->constrained('plants')
                ->onDelete('cascade');

            $table->double('unit_cost');
            $table->double('total_cost');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_details');
    }
};
