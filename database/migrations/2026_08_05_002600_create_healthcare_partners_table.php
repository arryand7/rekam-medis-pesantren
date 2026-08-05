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
        Schema::create('healthcare_partners', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('partner_type')->default('puskesmas'); // puskesmas, hospital, clinic, other
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('official_email')->nullable();
            $table->string('cooperation_reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('consultation_enabled')->default(true);
            $table->boolean('referral_enabled')->default(true);
            $table->string('default_channel')->default('fake_transport');
            $table->foreignUlid('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('updated_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('healthcare_partners');
    }
};
