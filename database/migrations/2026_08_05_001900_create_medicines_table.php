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
        Schema::create('medicines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->string('generic_name')->index();
            $table->string('brand_name')->nullable();
            $table->string('dosage_form'); // tablet, capsule, syrup, suspension, cream, ointment, drops, inhalation, injection, other
            $table->string('strength_text')->nullable();
            $table->string('base_unit'); // tablet, botol, tube, ampul, sachet
            $table->string('category')->nullable()->index();
            $table->text('description')->nullable();
            $table->unsignedInteger('minimum_stock')->default(10);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('requires_batch_tracking')->default(true);
            $table->boolean('requires_expiry_tracking')->default(true);
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
        Schema::dropIfExists('medicines');
    }
};
