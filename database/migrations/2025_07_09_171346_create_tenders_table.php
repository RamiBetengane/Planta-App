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
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_request_id')
                ->constrained('plant_request')
                ->unique()
                ->onDelete('cascade');

            $table->foreignId('manager_id')
                ->constrained('managers')
                ->onDelete('cascade');

            $table->dateTime('creation_date');
            $table->dateTime('open_date');
            $table->dateTime('close_date');
            $table->enum('status', ['open', 'closed', 'awarded']);
            $table->text('technical_requirements')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
