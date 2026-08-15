<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sso_configurations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->boolean('singleton')->default(true)->unique();
            $table->boolean('sso_enabled')->default(false);
            $table->string('driver', 20)->default('fake');
            $table->string('base_url', 500);
            $table->string('client_id', 255);
            $table->text('client_secret')->nullable();
            $table->string('redirect_uri', 500);
            $table->string('scopes', 500);
            $table->string('app_code', 120);
            $table->unsignedSmallInteger('http_timeout')->default(5);
            $table->unsignedTinyInteger('retry_attempts')->default(2);
            $table->unsignedSmallInteger('retry_backoff_ms')->default(200);
            $table->unsignedInteger('entitlement_ttl_seconds')->default(300);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_configurations');
    }
};
