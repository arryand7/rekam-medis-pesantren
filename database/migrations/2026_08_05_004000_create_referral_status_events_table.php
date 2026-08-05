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
        Schema::create('referral_status_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('referral_id')->constrained('referrals')->onDelete('cascade');
            $table->string('event_type')->index(); // arrived, accepted, declined, under_external_care, return_planned, returned
            $table->timestamp('occurred_at');
            $table->timestamp('received_at')->useCurrent();
            $table->string('source')->default('manual'); // manual, callback
            $table->foreignUlid('facility_partner_id')->nullable()->constrained('healthcare_partners')->onDelete('set null');
            $table->string('contact_attribution')->nullable(); // name/profession of person reporting
            $table->text('notes')->nullable();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('external_reference')->nullable(); // reference from external system if applicable
            $table->string('verification_status')->default('unverified'); // unverified, verified, disputed
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_status_events');
    }
};
