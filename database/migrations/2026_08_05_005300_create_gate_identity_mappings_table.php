<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gate_identity_mappings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('gate_user_id')->index();
            $table->foreignUlid('person_id')->constrained('people')->onDelete('restrict');
            $table->string('mapping_method')->default('approved_manual'); // exact_id, approved_manual, nis_match, nik_match
            $table->decimal('confidence_score', 3, 2)->default(1.00);
            $table->string('status')->default('pending')->index(); // pending, approved, rejected, deprecated
            $table->text('notes')->nullable();
            $table->foreignUlid('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['gate_user_id', 'person_id'], 'unique_gate_person_mapping');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_identity_mappings');
    }
};
