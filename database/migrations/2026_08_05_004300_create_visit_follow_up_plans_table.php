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
        Schema::create('visit_follow_up_plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('visit_discharge_id')->constrained('visit_discharges')->onDelete('cascade');
            $table->string('follow_up_type'); // poskestren_recheck, external_facility, activity_reassessment, medication_review, wound_review, other
            $table->dateTime('due_at')->nullable();
            $table->foreignUlid('healthcare_partner_id')->nullable()->constrained('healthcare_partners')->onDelete('set null');
            $table->text('instructions');
            $table->string('responsible_party_type')->nullable(); // dorm_supervisor, guardian, poskestren_staff, patient, other
            $table->string('responsible_party_reference')->nullable();
            $table->string('status')->default('planned')->index(); // planned, completed, cancelled, entered_in_error
            $table->foreignUlid('created_by_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('completed_at')->nullable();
            $table->foreignUlid('completed_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_follow_up_plans');
    }
};
