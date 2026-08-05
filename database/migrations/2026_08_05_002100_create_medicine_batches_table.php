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
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medicine_id')->constrained('medicines')->onDelete('restrict');
            $table->foreignUlid('stock_location_id')->constrained('stock_locations')->onDelete('restrict');
            $table->string('batch_number')->index();
            $table->date('expiry_date')->nullable()->index();
            $table->timestamp('received_at')->useCurrent();
            $table->string('supplier_name')->nullable();
            $table->string('purchase_reference')->nullable();
            $table->integer('initial_quantity')->default(0);
            $table->integer('current_quantity')->default(0)->index();
            $table->string('status')->default('active')->index(); // active, depleted, expired, quarantined, recalled, entered_in_error
            $table->foreignUlid('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->unique(['medicine_id', 'stock_location_id', 'batch_number'], 'unique_medicine_location_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_batches');
    }
};
