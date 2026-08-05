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
        Schema::create('referral_return_reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('referral_return_id')->constrained('referral_returns')->onDelete('cascade');
            $table->foreignUlid('local_reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('review_summary');
            $table->string('decision_type')->index(); // continue_poskestren_care, continue_observation, follow_up_external, rest_recommended, return_to_activity_recommended, new_referral_recommended, emergency_referral_required, other
            $table->text('medication_reconciliation_note')->nullable();
            $table->string('status')->default('finalized')->index();
            $table->timestamp('finalized_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_return_reviews');
    }
};
