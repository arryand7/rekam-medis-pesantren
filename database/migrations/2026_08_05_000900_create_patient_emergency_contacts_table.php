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
        Schema::create('patient_emergency_contacts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('patient_id')->constrained('patients')->onDelete('restrict');
            $table->string('name');
            $table->string('relationship');
            $table->string('phone');
            $table->integer('priority')->default(1);
            $table->string('source')->default('local'); // gate, local
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_emergency_contacts');
    }
};
