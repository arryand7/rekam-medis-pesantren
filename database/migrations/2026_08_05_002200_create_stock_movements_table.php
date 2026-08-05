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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medicine_id')->constrained('medicines')->onDelete('restrict');
            $table->foreignUlid('medicine_batch_id')->constrained('medicine_batches')->onDelete('restrict');
            $table->foreignUlid('stock_location_id')->constrained('stock_locations')->onDelete('restrict');
            $table->string('movement_type')->index(); // receipt, adjustment_in, adjustment_out, transfer_in, transfer_out, reversal
            $table->integer('quantity'); // Positive number representing mutation quantity
            $table->string('unit');
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason');
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->foreignUlid('reverses_movement_id')->nullable()->constrained('stock_movements')->onDelete('restrict');
            $table->string('correlation_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
