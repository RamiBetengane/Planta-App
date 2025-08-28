<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            // ربط مع tender (واحد tender له عدة offers)
            $table->foreignId('tender_id')
                ->constrained('tenders')
                ->onDelete('cascade');

            // ربط مع workshop (واحد workshop له عدة offers)
            $table->foreignId('workshop_id')
                ->constrained('workshops')
                ->onDelete('cascade');

            $table->double('total_offer_amount');       // المبلغ الكلي
            $table->integer('estimation_completion');   // مدة التنفيذ المقدرة (أيام مثلاً)

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
