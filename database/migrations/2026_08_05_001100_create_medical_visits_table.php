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
        Schema::create('medical_visits', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('visit_number')->unique()->index();
            $table->foreignUlid('patient_id')->constrained('patients')->onDelete('restrict');
            $table->string('status')->default('registered')->index(); // registered, waiting_assessment, cancelled
            $table->timestamp('arrived_at')->useCurrent()->index();
            $table->text('chief_complaint');
            $table->string('reporting_type')->default('self'); // self, dormitory_guardian, teacher, friend, other
            $table->string('reporting_name')->nullable();
            $table->string('origin_location')->nullable();
            $table->foreignUlid('receiving_officer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('assigned_officer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            $table->foreignUlid('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_visits');
    }
};
