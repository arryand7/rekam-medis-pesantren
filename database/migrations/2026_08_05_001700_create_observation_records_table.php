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
        Schema::create('observation_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('observation_episode_id')->constrained('observation_episodes')->onDelete('restrict');
            $table->timestamp('recorded_at')->useCurrent()->index();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('condition_summary');
            $table->text('symptom_changes')->nullable();
            $table->string('general_condition')->nullable(); // good, moderate, weak, critical
            $table->foreignUlid('vital_sign_id')->nullable()->constrained('vital_signs')->onDelete('set null');
            $table->text('fluid_intake_note')->nullable();
            $table->text('food_intake_note')->nullable();
            $table->text('elimination_note')->nullable();
            $table->text('activity_or_rest_note')->nullable();
            $table->text('follow_up_note')->nullable();
            $table->string('status')->default('finalized')->index(); // draft, finalized, entered_in_error
            $table->timestamp('finalized_at')->nullable();
            $table->foreignUlid('finalized_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('parent_record_id')->nullable()->constrained('observation_records')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_records');
    }
};
