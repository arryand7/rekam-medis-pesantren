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
        Schema::create('referral_transports', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('referral_id')->constrained('referrals')->onDelete('cascade');
            $table->string('transport_type')->default('school_vehicle'); // school_vehicle, ambulance_partner, external_ambulance, private_vehicle, other
            $table->string('vehicle_identifier')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_contact')->nullable();
            $table->foreignUlid('arranged_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('arranged_at')->useCurrent();
            $table->timestamp('departure_planned')->nullable();
            $table->timestamp('departure_actual')->nullable();
            $table->timestamp('arrival_actual')->nullable();
            $table->string('status')->default('planned')->index(); // planned, ready, departed, arrived, cancelled
            $table->text('notes')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_transports');
    }
};
