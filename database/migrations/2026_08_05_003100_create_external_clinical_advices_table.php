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
        Schema::create('external_clinical_advices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('clinical_consultation_id')->constrained('clinical_consultations')->onDelete('cascade');
            $table->foreignUlid('healthcare_partner_id')->constrained('healthcare_partners')->onDelete('restrict');
            $table->foreignUlid('recipient_contact_id')->nullable()->constrained('healthcare_partner_contacts')->onDelete('set null');
            $table->string('clinician_name');
            $table->string('clinician_profession');
            $table->string('clinician_identifier')->nullable(); // SIP/STR
            $table->string('department')->nullable();
            $table->timestamp('responded_at')->useCurrent();
            $table->timestamp('received_at')->useCurrent();
            $table->string('channel')->default('fake_transport');
            $table->text('advice_text');
            $table->text('limitations_text')->nullable();
            $table->text('recommended_next_step')->nullable();
            $table->string('verification_status')->default('verified')->index(); // unverified, partially_verified, verified, refuted
            $table->timestamp('verified_at')->nullable();
            $table->foreignUlid('verified_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('external_clinical_advices');
    }
};
