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
        Schema::create('clinical_consultation_transmissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('clinical_consultation_id')->constrained('clinical_consultations')->onDelete('cascade');
            $table->foreignUlid('clinical_consultation_version_id')->constrained('clinical_consultation_versions')->onDelete('cascade');
            $table->foreignUlid('healthcare_partner_id')->constrained('healthcare_partners')->onDelete('restrict');
            $table->foreignUlid('recipient_contact_id')->nullable()->constrained('healthcare_partner_contacts')->onDelete('set null');
            $table->string('channel')->default('fake_transport');
            $table->string('status')->default('sent')->index(); // queued, sending, sent, acknowledged, failed, cancelled
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('external_reference')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_code')->nullable();
            $table->text('failure_message_sanitized')->nullable();
            $table->foreignUlid('initiated_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('correlation_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_consultation_transmissions');
    }
};
