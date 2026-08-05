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
        Schema::create('medication_safety_acknowledgements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('patient_id')->constrained('patients')->onDelete('restrict');
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->foreignUlid('medication_order_id')->nullable()->constrained('medication_orders')->onDelete('cascade');
            $table->string('warning_type')->default('active_allergy_warning');
            $table->foreignUlid('allergy_reference_id')->nullable()->constrained('patient_allergies')->onDelete('set null');
            $table->text('warning_snapshot');
            $table->foreignUlid('acknowledged_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->useCurrent();
            $table->text('reason');
            $table->string('correlation_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_safety_acknowledgements');
    }
};
