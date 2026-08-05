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
        Schema::create('medication_administrations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->foreignUlid('medication_order_id')->nullable()->constrained('medication_orders')->onDelete('set null');
            $table->foreignUlid('medicine_id')->constrained('medicines')->onDelete('restrict');
            $table->foreignUlid('medicine_batch_id')->nullable()->constrained('medicine_batches')->onDelete('set null');
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->string('status')->default('scheduled')->index(); // scheduled, administered, held, refused, missed, cancelled, entered_in_error
            $table->string('dose_value');
            $table->string('dose_unit');
            $table->string('route')->default('oral');
            $table->timestamp('administered_at')->nullable();
            $table->foreignUlid('administered_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('recorded_at')->useCurrent();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUlid('stock_movement_id')->nullable()->constrained('stock_movements')->onDelete('set null');
            $table->string('idempotency_key')->nullable()->unique();
            $table->foreignUlid('parent_administration_id')->nullable()->constrained('medication_administrations')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_administrations');
    }
};
