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
        Schema::create('therapy_plan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapy_plan_id')->constrained()->cascadeOnDelete();
            $table->time('scheduled_time');
            $table->json('week_days')->nullable();
            $table->string('timezone')->default('Europe/Rome');
            $table->timestamps();

            $table->unique(['therapy_plan_id', 'scheduled_time'], 'therapy_plan_time_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapy_plan_schedules');
    }
};
