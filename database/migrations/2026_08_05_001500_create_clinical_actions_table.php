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
        Schema::create('clinical_actions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->foreignUlid('clinical_assessment_id')->nullable()->constrained('clinical_assessments')->onDelete('set null');
            $table->string('action_type')->default('first_aid'); // first_aid, wound_care, hydration, rest_recommendation, monitoring, procedure, other
            $table->text('description');
            $table->timestamp('performed_at')->useCurrent()->index();
            $table->foreignUlid('performed_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('performed')->index(); // performed, cancelled, entered_in_error
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_actions');
    }
};
