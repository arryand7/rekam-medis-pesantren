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
        Schema::create('integration_outbox_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('event_type'); // health_disposition_published, health_disposition_superseded, operational_notification_dispatched, etc.
            $table->string('aggregate_type'); // VisitDischarge, ActivityRestriction, OperationalNotification, etc.
            $table->string('aggregate_id');
            $table->string('destination'); // attendance_system, dorm_system, etc.
            $table->json('payload_snapshot');
            $table->unsignedInteger('payload_version')->default(1);
            $table->string('idempotency_key');
            $table->string('status')->default('pending'); // pending, processing, sent, acknowledged, failed, dead_letter, cancelled
            $table->dateTime('available_at');
            $table->unsignedInteger('attempt_count')->default(0);
            $table->dateTime('last_attempt_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('acknowledged_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->string('last_error_code')->nullable();
            $table->text('last_error_message_sanitized')->nullable();
            $table->string('correlation_id');
            $table->foreignUlid('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['destination', 'idempotency_key'], 'outbox_destination_idempotency_unique');
            $table->index(['status', 'available_at'], 'outbox_status_available_index');
            $table->index(['aggregate_type', 'aggregate_id'], 'outbox_aggregate_index');
            $table->index('correlation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_outbox_events');
    }
};
