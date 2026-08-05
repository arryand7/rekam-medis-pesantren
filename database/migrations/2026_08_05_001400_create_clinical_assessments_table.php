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
        Schema::create('clinical_assessments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->foreignUlid('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('history_current_illness');
            $table->text('relevant_history')->nullable();
            $table->text('examination_findings');
            $table->text('assessment_summary');
            $table->text('working_diagnosis')->nullable();
            $table->string('status')->default('draft')->index(); // draft, finalized, amended, entered_in_error
            $table->string('disposition_recommendation')->nullable();
            $table->foreignUlid('parent_assessment_id')->nullable()->constrained('clinical_assessments')->onDelete('set null');
            $table->text('amendment_reason')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUlid('finalized_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_assessments');
    }
};
