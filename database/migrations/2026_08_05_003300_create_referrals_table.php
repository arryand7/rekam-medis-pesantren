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
        Schema::create('referrals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->foreignUlid('clinical_assessment_id')->constrained('clinical_assessments')->onDelete('restrict');
            $table->foreignUlid('observation_episode_id')->nullable()->constrained('observation_episodes')->onDelete('set null');
            $table->foreignUlid('clinical_consultation_id')->nullable()->constrained('clinical_consultations')->onDelete('set null');
            $table->foreignUlid('consultation_local_decision_id')->nullable()->constrained('consultation_local_decisions')->onDelete('set null');
            $table->foreignUlid('healthcare_partner_id')->constrained('healthcare_partners')->onDelete('restrict');
            $table->foreignUlid('recipient_contact_id')->nullable()->constrained('healthcare_partner_contacts')->onDelete('set null');
            $table->string('referral_number')->unique();
            $table->string('urgency')->default('routine')->index(); // routine, urgent, emergency
            $table->text('reason');
            $table->text('clinical_summary');
            $table->string('requested_service_or_department')->nullable();
            $table->string('status')->default('prepared')->index(); // draft, prepared, approved, ready_to_depart, departed, arrived, accepted, under_external_care, return_planned, returned, completed, cancelled, declined_by_destination, superseded, entered_in_error
            $table->foreignUlid('initiated_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('initiated_at')->useCurrent();
            $table->foreignUlid('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at_destination')->nullable();
            $table->timestamp('accepted_at_destination')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignUlid('cancelled_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            $table->foreignUlid('supersedes_referral_id')->nullable()->constrained('referrals')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
