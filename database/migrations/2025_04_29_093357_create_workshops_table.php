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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // user_id مربوط بجدول users
            $table->integer('years_of_experience')->nullable(); // ممكن يكون فاضي
            $table->decimal('rating', 3, 2)->nullable(); // مثل 4.50 أو 3.75
            $table->string('specialization', 100)->nullable(); // تخصص الورشة، مسموح يكون فاضي
            $table->string('license_number', 50); // رقم الرخصة، إجباري
            $table->string('workshop_name', 100); // اسم الورشة، إجباري
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
