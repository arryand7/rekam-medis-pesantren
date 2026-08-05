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
        Schema::create('medication_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('restrict');
            $table->foreignUlid('clinical_assessment_id')->nullable()->constrained('clinical_assessments')->onDelete('set null');
            $table->foreignUlid('medicine_id')->constrained('medicines')->onDelete('restrict');
            $table->string('dose_value');
            $table->string('dose_unit');
            $table->string('route')->default('oral'); // oral, topical, inhalation, sublingual, other
            $table->string('frequency_text'); // e.g., 3x1 sehari sesudah makan
            $table->text('instructions')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedInteger('quantity_per_administration')->default(1);
            $table->foreignUlid('ordered_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('ordered_at')->useCurrent()->index();
            $table->string('status')->default('active')->index(); // draft, active, completed, discontinued, cancelled, entered_in_error
            $table->text('reason_or_indication')->nullable();
            $table->timestamp('discontinued_at')->nullable();
            $table->foreignUlid('discontinued_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('discontinuation_reason')->nullable();
            $table->foreignUlid('parent_order_id')->nullable()->constrained('medication_orders')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_orders');
    }
};
