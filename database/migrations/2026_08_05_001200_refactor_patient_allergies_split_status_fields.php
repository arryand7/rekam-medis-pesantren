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
        Schema::table('patient_allergies', function (Blueprint $table) {
            $table->string('clinical_status')->default('active')->index()->after('severity');
            $table->string('verification_status')->default('confirmed')->index()->after('clinical_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_allergies', function (Blueprint $table) {
            $table->dropColumn(['clinical_status', 'verification_status']);
        });
    }
};
