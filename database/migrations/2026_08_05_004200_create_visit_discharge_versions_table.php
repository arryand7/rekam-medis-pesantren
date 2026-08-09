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
        Schema::create('visit_discharge_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('visit_discharge_id')->constrained('visit_discharges')->onDelete('cascade');
            $table->unsignedInteger('version_number');
            $table->json('summary_payload');
            $table->string('checksum');
            $table->foreignUlid('authored_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('finalized_at')->useCurrent();
            $table->foreignUlid('supersedes_version_id')->nullable()->constrained('visit_discharge_versions')->onDelete('set null');
            $table->text('redaction_notes')->nullable();

            // Private document fields
            $table->string('document_path')->nullable();
            $table->string('document_disk')->default('discharge_documents');
            $table->string('document_mime')->nullable();
            $table->unsignedBigInteger('document_size')->nullable();
            $table->string('document_checksum')->nullable();
            $table->string('document_status')->default('none'); // none, generating, generated, generation_failed
            $table->timestamp('generated_at')->nullable();
            $table->foreignUlid('generated_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['visit_discharge_id', 'version_number'], 'discharge_ver_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_discharge_versions');
    }
};
