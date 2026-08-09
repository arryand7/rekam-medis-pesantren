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
        Schema::create('integration_delivery_attempts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('outbox_event_id')->constrained('integration_outbox_events')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number');
            $table->string('destination');
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->string('result'); // success, transient_failure, permanent_failure, dead_lettered
            $table->string('external_reference')->nullable();
            $table->unsignedSmallInteger('http_status_code')->nullable();
            $table->text('sanitized_error')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('correlation_id');
            $table->timestamps();

            $table->index(['outbox_event_id', 'attempt_number'], 'delivery_attempt_event_num_idx');
            $table->index('correlation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_delivery_attempts');
    }
};
