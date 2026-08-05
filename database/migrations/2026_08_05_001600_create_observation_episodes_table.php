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
        Schema::create('observation_episodes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->text('reason');
            $table->string('status')->default('active')->index(); // planned, active, completed, transferred, cancelled, entered_in_error
            $table->timestamp('started_at')->useCurrent()->index();
            $table->foreignUlid('started_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('responsible_officer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('location_label')->nullable();
            $table->string('bed_label')->nullable();
            $table->unsignedInteger('monitoring_interval_minutes')->nullable();
            $table->timestamp('next_monitoring_due_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable();
            $table->foreignUlid('ended_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('outcome')->nullable();
            $table->text('outcome_reason')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_episodes');
    }
};
