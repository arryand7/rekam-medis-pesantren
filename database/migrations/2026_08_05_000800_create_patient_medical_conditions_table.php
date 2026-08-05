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
        Schema::create('patient_medical_conditions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('patient_id')->constrained('patients')->onDelete('restrict');
            $table->string('condition_name')->index();
            $table->string('status')->default('active')->index(); // active, inactive, resolved, entered-in-error
            $table->date('onset_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_medical_conditions');
    }
};
