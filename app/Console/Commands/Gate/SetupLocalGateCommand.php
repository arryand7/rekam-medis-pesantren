<?php

namespace App\Console\Commands\Gate;

use App\Services\SsoConfigurationService;
use Illuminate\Console\Command;

class SetupLocalGateCommand extends Command
{
    protected $signature = 'gate:setup-local
        {--base-url=http://localhost:8001 : Base URL server Gate SSO}
        {--client-id= : Client ID aplikasi POSKESTREN di Gate SSO}
        {--client-secret= : Client Secret aplikasi POSKESTREN di Gate SSO}
        {--app-code=poskestren-rekam-medis : App code / slug aplikasi di Gate SSO}
        {--activate : Aktifkan SSO (jika tidak disertakan, hanya konfigurasi sync, SSO login tetap nonaktif)}';

    protected $description = 'Konfigurasi koneksi ke Gate SSO lokal untuk keperluan development/staging sync.';

    public function __construct(
        private readonly SsoConfigurationService $ssoService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $clientId = $this->option('client-id');
        $clientSecret = $this->option('client-secret');

        if (empty($clientId) || empty($clientSecret)) {
            $this->error('Opsi --client-id dan --client-secret wajib diisi.');
            $this->line('');
            $this->line('Contoh:');
            $this->line('  php artisan gate:setup-local \\');
            $this->line('    --client-id=<CLIENT_ID> \\');
            $this->line('    --client-secret=<CLIENT_SECRET>');

            return self::FAILURE;
        }

        $baseUrl = rtrim((string) $this->option('base-url'), '/');
        $appCode = (string) $this->option('app-code');
        $activate = (bool) $this->option('activate');

        $this->info('Mengkonfigurasi koneksi Gate SSO lokal...');
        $this->line("  Base URL    : {$baseUrl}");
        $this->line('  Client ID   : '.substr($clientId, 0, 8).'...');
        $this->line('  App Code    : '.$appCode);
        $this->line('  SSO Aktif   : '.($activate ? 'Ya' : 'Tidak (sync-only mode)'));

        try {
            $this->ssoService->update([
                'driver' => 'http',
                'base_url' => $baseUrl,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'app_code' => $appCode,
                'redirect_uri' => config('app.url').'/auth/gate/callback',
                'scopes' => 'openid profile email',
                'sso_enabled' => $activate,
                'http_timeout' => 10,
                'retry_attempts' => 2,
                'retry_backoff_ms' => 300,
                'entitlement_ttl_seconds' => 300,
            ]);

            $this->info('✓ Konfigurasi Gate SSO berhasil disimpan.');

            // Test koneksi
            $this->line('');
            $this->line('Menguji koneksi ke Gate SSO...');

            $gateClient = app(\App\Contracts\GateClientContract::class);
            $ok = $gateClient->ping();

            if ($ok) {
                $this->info('✓ Koneksi Gate SSO berhasil!');

                // Coba ambil daftar user
                try {
                    $payload = $gateClient->fetchUsers(1, 5);
                    $total = $payload['total_items'] ?? count($payload['data']);
                    $this->info("✓ API provisioning aktif — {$total} pengguna tersedia untuk disinkronisasi.");
                } catch (\Throwable $e) {
                    $this->warn('⚠ Koneksi OK tapi API provisioning belum siap: '.$e->getMessage());
                }
            } else {
                $this->warn('⚠ Tidak dapat terhubung ke Gate SSO. Pastikan server berjalan di: '.$baseUrl);
                $this->warn('  Jalankan: cd /path/to/gate-sso && php artisan serve --port=8001');
            }

            $this->line('');
            $this->line('Untuk menjalankan sinkronisasi:');
            $this->line('  Buka halaman: /gate/sync');
            $this->line('  Atau gunakan Artisan: php artisan gate:sync-apply');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal menyimpan konfigurasi: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
