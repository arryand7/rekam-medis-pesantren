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
        Schema::create('referral_companions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('referral_id')->constrained('referrals')->onDelete('cascade');
            $table->foreignUlid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name_snapshot');
            $table->string('role_relationship'); // e.g. Petugas Kesehatan Poskestren / Pengasuh Asrama
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->foreignUlid('assigned_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->useCurrent();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_companions');
    }
};
