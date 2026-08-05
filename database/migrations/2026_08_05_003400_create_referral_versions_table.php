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
        Schema::create('referral_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('referral_id')->constrained('referrals')->onDelete('cascade');
            $table->unsignedInteger('version_number');
            $table->json('summary_payload');
            $table->string('checksum');
            $table->foreignUlid('authored_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('finalized_at')->useCurrent();
            $table->foreignUlid('supersedes_version_id')->nullable()->constrained('referral_versions')->onDelete('set null');
            $table->text('redaction_notes')->nullable();
            $table->timestamps();

            $table->unique(['referral_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_versions');
    }
};
