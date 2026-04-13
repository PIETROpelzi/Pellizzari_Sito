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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('remaining_quantity')->default(0);
            $table->decimal('minimum_temperature', 5, 2)->nullable();
            $table->decimal('maximum_temperature', 5, 2)->nullable();
            $table->decimal('minimum_humidity', 5, 2)->nullable();
            $table->decimal('maximum_humidity', 5, 2)->nullable();
            $table->unsignedInteger('reorder_threshold')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
