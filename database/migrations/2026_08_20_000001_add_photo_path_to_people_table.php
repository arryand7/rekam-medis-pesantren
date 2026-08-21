<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table): void {
            $table->string('photo_path')->nullable()->after('synced_at')
                ->comment('Path foto profil relatif ke storage/app/private/person-photos/');
            $table->string('photo_checksum', 64)->nullable()->after('photo_path')
                ->comment('SHA-256 checksum foto dari Gate SSO, digunakan untuk deteksi perubahan');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table): void {
            $table->dropColumn(['photo_path', 'photo_checksum']);
        });
    }
};
