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
        Schema::create('people', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('gate_user_id')->nullable()->unique()->index();
            $table->string('name');
            $table->string('nik')->nullable()->index();
            $table->string('nis_nip')->nullable()->index();
            $table->string('user_type')->default('santri')->index(); // santri, guru, staf, pengasuh, petugas_kesehatan, admin, service_account, bot
            $table->string('gender', 1)->nullable(); // L / P
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('source_status')->default('active')->index(); // active, inactive, suspended
            $table->timestamp('source_updated_at')->nullable();
            $table->string('source_version')->nullable();
            $table->string('checksum')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
