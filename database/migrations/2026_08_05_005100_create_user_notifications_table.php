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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('notification_type'); // follow_up_due, operational_handoff_pending, dead_letter_alert, referral_alert, system_alert
            $table->string('title');
            $table->text('body');
            $table->json('payload_snapshot')->nullable();
            $table->string('source_type')->nullable(); // MedicalVisit, VisitFollowUpPlan, IntegrationOutboxEvent, etc.
            $table->string('source_id')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->dateTime('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at'], 'user_notif_user_read_idx');
            $table->index(['source_type', 'source_id'], 'user_notif_source_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
