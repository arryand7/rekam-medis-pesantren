<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gate_sync_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('run_type')->default('dry_run'); // dry_run, apply
            $table->string('status')->default('running')->index(); // running, completed, failed, partial
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('applied_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('conflict_count')->default(0);
            $table->json('summary_json');
            $table->string('source_version_cursor')->nullable();
            $table->foreignUlid('executed_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_sync_runs');
    }
};
