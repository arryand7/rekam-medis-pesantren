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
        Schema::create('clinical_consultations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->foreignUlid('clinical_assessment_id')->constrained('clinical_assessments')->onDelete('restrict');
            $table->foreignUlid('observation_episode_id')->nullable()->constrained('observation_episodes')->onDelete('set null');
            $table->foreignUlid('healthcare_partner_id')->constrained('healthcare_partners')->onDelete('restrict');
            $table->foreignUlid('recipient_contact_id')->nullable()->constrained('healthcare_partner_contacts')->onDelete('set null');
            $table->string('purpose');
            $table->text('clinical_question');
            $table->string('urgency')->default('routine')->index(); // routine, urgent, emergency
            $table->string('status')->default('draft')->index(); // draft, ready, sent, acknowledged, responded, completed, cancelled, superseded_by_referral, entered_in_error
            $table->foreignUlid('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUlid('finalized_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('sent_at')->nullable();
            $table->foreignUlid('sent_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignUlid('completed_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignUlid('cancelled_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_consultations');
    }
};
