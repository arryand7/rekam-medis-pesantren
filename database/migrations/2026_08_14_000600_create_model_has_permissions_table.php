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
        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->foreignUlid('permission_id')->constrained('permissions')->onDelete('cascade');
                $table->foreignUlid('model_id')->index(); // Points to users.id
                $table->string('model_type')->default('App\\Models\\User');
                $table->primary(['permission_id', 'model_id', 'model_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_permissions');
    }
};
