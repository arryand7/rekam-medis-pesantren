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
        Schema::create('patient_allergies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('patient_id')->constrained('patients')->onDelete('restrict');
            $table->string('allergen')->index();
            $table->string('reaction')->nullable();
            $table->string('severity')->nullable(); // mild, moderate, severe, life-threatening
            $table->string('status')->default('confirmed')->index(); // suspected, confirmed, resolved, entered-in-error
            $table->text('notes')->nullable();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('verified_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_allergies');
    }
};
