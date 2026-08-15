<x-app-layout>
    <x-slot name="title">Pengaturan Gate SSO</x-slot>

    <div class="space-y-6">
        <div class="ui-card p-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[var(--primary)]">Administrasi & Sistem</p>
                <h1 class="mt-1 text-2xl font-bold text-[var(--foreground)]">Pengaturan Gate SSO</h1>
                <p class="mt-2 max-w-3xl text-sm text-[var(--foreground-secondary)]">Kelola koneksi OpenID Connect tanpa mengubah file <code>.env</code>. Client secret disimpan terenkripsi dan tidak pernah ditampilkan kembali.</p>
            </div>
            <div class="flex flex-wrap gap-2" aria-label="Status konfigurasi SSO">
                <span class="ui-badge {{ $settings['sso_enabled'] ? 'ui-badge-success' : 'ui-badge-neutral' }}">{{ $settings['sso_enabled'] ? 'SSO Aktif' : 'SSO Nonaktif' }}</span>
                <span class="ui-badge {{ $settings['is_ready'] ? 'ui-badge-info' : 'ui-badge-warning' }}">{{ $settings['is_ready'] ? 'Konfigurasi Lengkap' : 'Belum Lengkap' }}</span>
                <span class="ui-badge ui-badge-neutral">{{ strtoupper($settings['driver']) }}</span>
            </div>
        </div>

        @if(session('success'))
            <div class="ui-banner ui-banner-success" role="status">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="ui-banner ui-banner-danger" role="alert">
                <div class="font-bold">Pengaturan belum dapat disimpan.</div>
                <ul class="mt-2 list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.system.sso-configuration.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="ui-card p-6">
                <h2 class="text-lg font-bold text-[var(--foreground)]">Aktivasi & Mode Koneksi</h2>
                <p class="ui-text-secondary mt-1">Siapkan dan simpan konfigurasi lebih dahulu. Aktifkan SSO hanya setelah callback telah didaftarkan pada Gate.</p>

                <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div>
                        <label for="driver" class="ui-form-label text-sm">Mode integrasi</label>
                        <select id="driver" name="driver" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5">
                            <option value="fake" @selected(old('driver', $settings['driver']) === 'fake')>Simulasi lokal (aman)</option>
                            <option value="http" @selected(old('driver', $settings['driver']) === 'http')>Server Gate (HTTP/OIDC)</option>
                        </select>
                        <p class="ui-form-hint">Mode simulasi tidak menghubungi server eksternal dan tidak dapat mengaktifkan SSO.</p>
                    </div>

                    <div class="rounded-xl border border-[var(--border)] bg-[var(--surface-subtle)] p-4">
                        <input type="hidden" name="sso_enabled" value="0">
                        <label class="flex items-start gap-3 cursor-pointer" for="sso_enabled">
                            <input id="sso_enabled" name="sso_enabled" type="checkbox" value="1" @checked(old('sso_enabled', $settings['sso_enabled'])) class="mt-1 rounded border-[var(--border)] text-[var(--primary)] focus:ring-[var(--focus-ring)]">
                            <span>
                                <span class="block text-sm font-bold text-[var(--foreground)]">Aktifkan tombol dan callback Gate SSO</span>
                                <span class="block mt-1 text-xs text-[var(--foreground-secondary)]">Sistem akan menolak aktivasi jika mode HTTP, endpoint, Client ID, secret, scope OIDC, atau callback belum valid.</span>
                            </span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="ui-card p-6">
                <h2 class="text-lg font-bold text-[var(--foreground)]">Provider OpenID Connect</h2>
                <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="lg:col-span-2">
                        <label for="base_url" class="ui-form-label text-sm">URL dasar Gate</label>
                        <input id="base_url" name="base_url" type="url" required maxlength="500" value="{{ old('base_url', $settings['base_url']) }}" placeholder="https://gate.example.sch.id" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5 font-mono">
                        <p class="ui-form-hint">Gunakan HTTPS. Endpoint authorize, token, UserInfo, dan entitlement mengikuti kontrak Gate yang dikendalikan source code.</p>
                    </div>

                    <div>
                        <label for="client_id" class="ui-form-label text-sm">Client ID</label>
                        <input id="client_id" name="client_id" type="text" maxlength="255" autocomplete="off" value="{{ old('client_id', $settings['client_id']) }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5 font-mono">
                    </div>

                    <div>
                        <label for="client_secret" class="ui-form-label text-sm">Client secret</label>
                        <input id="client_secret" name="client_secret" type="password" minlength="16" maxlength="4096" autocomplete="new-password" placeholder="{{ $settings['client_secret_configured'] ? 'Tersimpan — kosongkan untuk mempertahankan' : 'Masukkan client secret' }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5 font-mono">
                        <p class="ui-form-hint">{{ $settings['client_secret_configured'] ? 'Secret terenkripsi sudah tersedia. Isi hanya untuk melakukan rotasi.' : 'Belum ada secret tersimpan.' }}</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="redirect_uri" class="ui-form-label text-sm">Callback / Redirect URI</label>
                        <input id="redirect_uri" name="redirect_uri" type="url" required maxlength="500" value="{{ old('redirect_uri', $settings['redirect_uri'] ?: $suggestedCallback) }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5 font-mono">
                        <p class="ui-form-hint">Daftarkan nilai ini persis di Gate. Saran berdasarkan URL aplikasi saat ini: <code>{{ $suggestedCallback }}</code></p>
                    </div>

                    <div>
                        <label for="scopes" class="ui-form-label text-sm">OIDC scopes</label>
                        <input id="scopes" name="scopes" type="text" required maxlength="500" value="{{ old('scopes', $settings['scopes']) }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5 font-mono">
                        <p class="ui-form-hint"><code>openid</code> wajib. Gunakan spasi sebagai pemisah scope.</p>
                    </div>

                    <div>
                        <label for="app_code" class="ui-form-label text-sm">Application code / entitlement</label>
                        <input id="app_code" name="app_code" type="text" required maxlength="120" value="{{ old('app_code', $settings['app_code']) }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5 font-mono">
                        <p class="ui-form-hint">Harus sama dengan kode aplikasi yang diberikan Gate.</p>
                    </div>
                </div>
            </section>

            <section class="ui-card p-6">
                <h2 class="text-lg font-bold text-[var(--foreground)]">Keandalan & Sesi</h2>
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div>
                        <label for="http_timeout" class="ui-form-label text-sm">Timeout HTTP (detik)</label>
                        <input id="http_timeout" name="http_timeout" type="number" min="2" max="30" required value="{{ old('http_timeout', $settings['http_timeout']) }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5">
                    </div>
                    <div>
                        <label for="retry_attempts" class="ui-form-label text-sm">Percobaan ulang</label>
                        <input id="retry_attempts" name="retry_attempts" type="number" min="0" max="5" required value="{{ old('retry_attempts', $settings['retry_attempts']) }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5">
                    </div>
                    <div>
                        <label for="retry_backoff_ms" class="ui-form-label text-sm">Jeda retry (ms)</label>
                        <input id="retry_backoff_ms" name="retry_backoff_ms" type="number" min="0" max="5000" required value="{{ old('retry_backoff_ms', $settings['retry_backoff_ms']) }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5">
                    </div>
                    <div>
                        <label for="entitlement_ttl_seconds" class="ui-form-label text-sm">TTL entitlement (detik)</label>
                        <input id="entitlement_ttl_seconds" name="entitlement_ttl_seconds" type="number" min="60" max="3600" required value="{{ old('entitlement_ttl_seconds', $settings['entitlement_ttl_seconds']) }}" class="ui-form-control w-full rounded-xl px-3 py-2.5 mt-1.5">
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="submit" class="inline-flex justify-center rounded-xl bg-[var(--primary)] px-5 py-3 text-sm font-bold text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--focus-ring)] focus:ring-offset-2">Simpan Pengaturan SSO</button>
            </div>
        </form>

        <section class="ui-card p-6 border-rose-200 dark:border-rose-900">
            <h2 class="text-lg font-bold text-[var(--foreground)]">Kembalikan ke Default Aman</h2>
            <p class="ui-text-secondary mt-1">Menghapus konfigurasi persisten dan client secret. SSO kembali nonaktif dengan mode simulasi; konfigurasi source-controlled tetap dipertahankan.</p>
            <form method="POST" action="{{ route('admin.system.sso-configuration.reset') }}" class="mt-4" onsubmit="return confirm('Hapus konfigurasi dan client secret Gate SSO? SSO akan dinonaktifkan.');">
                @csrf
                <input type="hidden" name="confirm_reset" value="1">
                <button type="submit" class="rounded-xl border border-rose-300 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 px-4 py-2.5 text-sm font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-950 focus:outline-none focus:ring-2 focus:ring-rose-500">Reset Pengaturan SSO</button>
            </form>
        </section>
    </div>
</x-app-layout>
