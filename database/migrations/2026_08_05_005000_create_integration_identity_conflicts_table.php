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
        Schema::create('integration_identity_conflicts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->string('destination'); // attendance_system, dorm_system, etc.
            $table->string('conflict_type'); // missing_gate_user_id, unsupported_person_type, mapping_mismatch, etc.
            $table->json('source_identifier_snapshot');
            $table->string('status')->default('open'); // open, resolved, ignored
            $table->text('resolution_notes')->nullable();
            $table->foreignUlid('resolved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('resolved_at')->nullable();
            $table->string('correlation_id');
            $table->timestamps();

            $table->index(['destination', 'status'], 'id_conflict_dest_status_idx');
            $table->index('correlation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_identity_conflicts');
    }
};
