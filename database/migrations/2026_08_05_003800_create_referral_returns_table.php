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
        Schema::create('referral_returns', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('referral_id')->unique()->constrained('referrals')->onDelete('cascade');
            $table->timestamp('returned_at')->useCurrent();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('return_transport_notes')->nullable();
            $table->text('accompanied_by_notes')->nullable();
            $table->text('external_outcome_summary');
            $table->text('external_diagnosis_text')->nullable();
            $table->text('external_procedures_text')->nullable();
            $table->text('external_medication_instructions')->nullable();
            $table->text('restrictions_text')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('follow_up_facility')->nullable();
            $table->text('documents_received_notes')->nullable();
            $table->string('status')->default('returned')->index();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_returns');
    }
};
