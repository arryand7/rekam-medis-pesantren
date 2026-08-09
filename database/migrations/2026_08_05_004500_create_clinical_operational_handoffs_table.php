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
        Schema::create('clinical_operational_handoffs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('medical_visit_id')->constrained('medical_visits')->onDelete('cascade');
            $table->foreignUlid('visit_discharge_id')->constrained('visit_discharges')->onDelete('cascade');
            $table->string('recipient_type'); // dorm_supervisor, homeroom_teacher, guardian, patient, staff_supervisor, other
            $table->string('recipient_reference')->nullable();
            $table->string('purpose'); // dorm_care_instruction, class_absence_notice, guardian_health_update, work_duty_modification, other
            $table->json('payload_snapshot'); // minimum necessary care/restriction data
            $table->string('status')->default('ready')->index(); // draft, ready, acknowledged, cancelled, entered_in_error
            $table->foreignUlid('prepared_by_id')->constrained('users')->onDelete('restrict');
            $table->dateTime('prepared_at');
            $table->dateTime('acknowledged_at')->nullable();
            $table->foreignUlid('acknowledged_by_id')->nullable()->constrained('users')->onDelete('set null');

            $table->text('acknowledgement_notes')->nullable();
            $table->string('channel')->default('internal');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_operational_handoffs');
    }
};
