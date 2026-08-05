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
        Schema::create('observation_handovers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('observation_episode_id')->constrained('observation_episodes')->onDelete('restrict');
            $table->foreignUlid('from_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUlid('to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('prepared_at')->useCurrent()->index();
            $table->text('summary');
            $table->text('current_condition');
            $table->text('pending_tasks')->nullable();
            $table->text('risks_or_warnings')->nullable();
            $table->timestamp('next_monitoring_due_at')->nullable();
            $table->string('status')->default('submitted')->index(); // draft, submitted, acknowledged, cancelled, entered_in_error
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignUlid('acknowledged_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_handovers');
    }
};
