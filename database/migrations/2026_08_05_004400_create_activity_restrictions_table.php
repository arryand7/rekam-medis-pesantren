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
        Schema::create('activity_restrictions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('visit_discharge_id')->constrained('visit_discharges')->onDelete('cascade');
            $table->string('activity_status'); // full_activity, limited_activity, rest, temporarily_not_cleared, other
            $table->dateTime('effective_start');
            $table->dateTime('effective_until')->nullable();
            $table->string('restriction_type'); // bed_rest, light_duty_only, no_sports, no_heavy_lifting, dietary_restriction, isolation_rest, other
            $table->text('restriction_notes');
            $table->text('allowed_activity_notes')->nullable();
            $table->foreignUlid('issued_by_id')->constrained('users')->onDelete('restrict');
            $table->dateTime('issued_at');
            $table->dateTime('review_date')->nullable();

            $table->string('status')->default('active')->index(); // active, amended, cancelled, entered_in_error, expired
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_restrictions');
    }
};
