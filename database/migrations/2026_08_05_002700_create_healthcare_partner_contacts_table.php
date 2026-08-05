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
        Schema::create('healthcare_partner_contacts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('healthcare_partner_id')->constrained('healthcare_partners')->onDelete('cascade');
            $table->string('name');
            $table->string('profession'); // Dokter Spesialis, Dokter Umum, Perawat Consult
            $table->string('registration_identifier')->nullable(); // SIP/STR
            $table->string('department')->nullable();
            $table->string('official_contact')->nullable();
            $table->string('channel_type')->default('fake_transport');
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->foreignUlid('verified_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('healthcare_partner_contacts');
    }
};
