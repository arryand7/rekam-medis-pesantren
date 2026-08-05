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
        Schema::create('consultation_local_decisions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('clinical_consultation_id')->constrained('clinical_consultations')->onDelete('cascade');
            $table->foreignUlid('external_clinical_advice_id')->nullable()->constrained('external_clinical_advices')->onDelete('set null');
            $table->string('decision_type')->index(); // continue_current_care, continue_observation, return_to_activity_recommended, rest_recommended, follow_up_required, referral_recommended, emergency_referral_required, other
            $table->text('rationale');
            $table->foreignUlid('decided_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('decided_at')->useCurrent();
            $table->string('status')->default('finalized')->index(); // draft, finalized, amended, entered_in_error
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_local_decisions');
    }
};
