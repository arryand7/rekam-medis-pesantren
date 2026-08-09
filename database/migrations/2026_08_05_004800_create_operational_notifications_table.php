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
        Schema::create('operational_notifications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignUlid('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignUlid('medical_visit_id')->nullable()->constrained('medical_visits')->nullOnDelete();
            $table->foreignUlid('visit_discharge_id')->nullable()->constrained('visit_discharges')->nullOnDelete();
            $table->foreignUlid('activity_restriction_id')->nullable()->constrained('activity_restrictions')->nullOnDelete();
            $table->string('notification_type'); // health_visit_closed, rest_restriction, limited_activity, follow_up_required, return_to_activity, external_follow_up, operational_attention_required, other
            $table->string('recipient_type'); // dorm_supervisor, homeroom_teacher, guardian, patient, staff_supervisor, attendance_system, other
            $table->string('recipient_reference')->nullable();
            $table->json('payload_snapshot'); // minimum-necessary privacy payload
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('status')->default('prepared'); // prepared, ready, delivered, acknowledged, cancelled
            $table->foreignUlid('prepared_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('prepared_at');
            $table->dateTime('ready_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('acknowledged_at')->nullable();
            $table->foreignUlid('acknowledged_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('acknowledgement_notes')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('correlation_id');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->index(['person_id', 'status'], 'op_notif_person_status_idx');
            $table->index(['recipient_type', 'status'], 'op_notif_recipient_status_idx');
            $table->index(['medical_visit_id', 'notification_type'], 'op_notif_visit_type_idx');
            $table->index('correlation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_notifications');
    }
};
