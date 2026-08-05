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
        Schema::create('referral_handovers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('referral_id')->constrained('referrals')->onDelete('cascade');
            $table->foreignUlid('referral_version_id')->constrained('referral_versions')->onDelete('cascade');
            $table->foreignUlid('from_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('destination_partner_id')->constrained('healthcare_partners')->onDelete('restrict');
            $table->foreignUlid('recipient_contact_id')->nullable()->constrained('healthcare_partner_contacts')->onDelete('set null');
            $table->timestamp('handed_over_at')->useCurrent();
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('handed_over')->index(); // prepared, handed_over, acknowledged, failed, cancelled
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_handovers');
    }
};
