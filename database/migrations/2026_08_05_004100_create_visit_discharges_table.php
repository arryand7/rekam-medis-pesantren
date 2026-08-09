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
        Schema::create('visit_discharges', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->string('discharge_type'); // return_to_activity, rest_required, continue_poskestren_care, follow_up_external, referred_again, transfer_of_care, other
            $table->string('discharge_destination'); // dormitory, home_with_guardian, class, workplace_staff, external_facility, other
            $table->text('clinical_summary');
            $table->string('final_condition'); // recovered, improved, unchanged, referred, other
            $table->string('activity_recommendation'); // full_activity, limited_activity, rest, temporarily_not_cleared, other
            $table->text('rest_recommendation')->nullable();
            $table->text('restriction_notes')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->text('follow_up_summary')->nullable();
            $table->dateTime('follow_up_date')->nullable();
            $table->foreignUlid('follow_up_partner_id')->nullable()->constrained('healthcare_partners')->onDelete('set null');
            $table->foreignUlid('prepared_by_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('prepared_at');
            $table->foreignUlid('finalized_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('finalized_at')->nullable();
            $table->string('status')->default('draft')->index(); // draft, finalized, amended, entered_in_error
            $table->foreignUlid('parent_discharge_id')->nullable()->constrained('visit_discharges')->onDelete('set null');
            $table->text('amendment_reason')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->unique('medical_visit_id', 'visit_discharges_visit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_discharges');
    }
};
