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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dispenser_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sensor_log_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dose_log_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->index();
            $table->string('severity')->default('Medium')->index();
            $table->string('message');
            $table->timestamp('triggered_at')->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->boolean('notified_caregiver')->default(false);
            $table->boolean('notified_doctor')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
