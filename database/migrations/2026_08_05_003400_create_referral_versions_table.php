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
            // Private document fields (stored on referral_documents disk, never public)
            $table->string('document_path')->nullable();             // relative path within private disk
            $table->string('document_disk')->default('referral_documents'); // disk identifier
            $table->string('document_mime')->nullable();             // e.g. application/pdf
            $table->unsignedBigInteger('document_size')->nullable(); // bytes
            $table->string('document_checksum')->nullable();         // SHA-256 of file content
            $table->string('document_status')->default('none');      // none, generating, generated, generation_failed
            $table->timestamp('generated_at')->nullable();
            $table->foreignUlid('generated_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['referral_id', 'version_number'], 'referral_ver_unique');
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
