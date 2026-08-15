<x-app-layout>
    <x-slot name="title">Identitas Aplikasi</x-slot>

    <div class="space-y-6" x-data="{ resetConfirmed: false }">
        <header class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[var(--primary)]">Administrasi & Sistem</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-[var(--foreground)]">Identitas Aplikasi</h1>
                    <p class="mt-2 max-w-2xl text-sm text-[var(--foreground-muted)]">Kelola nama, institusi, logo, favicon, dan identitas footer yang tampil di seluruh aplikasi.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full border border-[var(--border)] bg-[var(--surface-muted)] px-3 py-1.5 text-xs font-semibold text-[var(--foreground)]">
                    {{ $identity['is_customized'] ? 'Identitas dikustomisasi' : 'Menggunakan default aman' }}
                </span>
            </div>
        </header>

        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200">
                <p class="font-semibold">Periksa kembali data identitas aplikasi.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.system.application-identity.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 sm:p-6 shadow-xs">
                <h2 class="text-base font-bold text-[var(--foreground)]">Identitas Utama</h2>
                <p class="mt-1 text-sm text-[var(--foreground-muted)]">Nama pendek dipakai pada area navigasi yang ruangnya terbatas.</p>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    @foreach ([
                        ['application_name', 'Nama Aplikasi', 120],
                        ['application_short_name', 'Nama Pendek', 50],
                        ['institution_name', 'Nama Institusi', 160],
                        ['tagline', 'Tagline', 160],
                    ] as [$field, $label, $max])
                        <div>
                            <label for="{{ $field }}" class="ui-form-label">{{ $label }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $identity[$field]) }}" maxlength="{{ $max }}" required class="ui-form-control w-full rounded-xl px-3 py-2.5" aria-describedby="{{ $field }}-hint">
                            <p id="{{ $field }}-hint" class="ui-form-hint">Maksimal {{ $max }} karakter.</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 sm:p-6 shadow-xs">
                <h2 class="text-base font-bold text-[var(--foreground)]">Deskripsi & Footer</h2>
                <div class="mt-5 grid gap-5">
                    <div>
                        <label for="description" class="ui-form-label">Deskripsi</label>
                        <textarea id="description" name="description" rows="4" maxlength="1000" class="ui-form-control w-full rounded-xl px-3 py-2.5" aria-describedby="description-hint">{{ old('description', $identity['description']) }}</textarea>
                        <p id="description-hint" class="ui-form-hint">Deskripsi internal singkat, maksimal 1.000 karakter.</p>
                    </div>
                    <div>
                        <label for="footer_text" class="ui-form-label">Footer Text</label>
                        <input id="footer_text" name="footer_text" value="{{ old('footer_text', $identity['footer_text']) }}" maxlength="255" class="ui-form-control w-full rounded-xl px-3 py-2.5" aria-describedby="footer-hint">
                        <p id="footer-hint" class="ui-form-hint">Teks identitas publik. Tahun ditambahkan otomatis.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 sm:p-6 shadow-xs">
                <h2 class="text-base font-bold text-[var(--foreground)]">Logo & Ikon</h2>
                <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-800 dark:bg-sky-950/50 dark:text-sky-100">
                    <p class="font-semibold">Aset branding bersifat publik.</p>
                    <p class="mt-1">Unggah hanya PNG, JPEG, atau WebP. SVG upload ditolak karena dapat memuat konten aktif. Jangan unggah logo privat atau data medis.</p>
                </div>
                <div class="mt-5 grid gap-5 lg:grid-cols-3">
                    @foreach ([
                        ['logo', 'Logo Utama', 'PNG/JPEG/WebP, maksimal 2 MB.'],
                        ['logo_dark', 'Logo Dark Mode', 'Opsional; fallback ke logo utama lalu default dark.'],
                        ['favicon', 'Favicon / App Icon', 'PNG/JPEG/WebP, maksimal 1 MB.'],
                    ] as [$field, $label, $hint])
                        <div>
                            <label for="{{ $field }}" class="ui-form-label">{{ $label }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="file" accept="image/png,image/jpeg,image/webp" class="ui-form-control w-full rounded-xl px-3 py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-sky-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-sky-800 dark:file:bg-sky-950 dark:file:text-sky-200" aria-describedby="{{ $field }}-hint">
                            <p id="{{ $field }}-hint" class="ui-form-hint">{{ $hint }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 sm:p-6 shadow-xs">
                <h2 class="text-base font-bold text-[var(--foreground)]">Preview</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <p class="mb-3 text-xs font-semibold text-slate-600">Light surface</p>
                        <img src="{{ $identity['logo_url'] }}" alt="Preview logo light" class="h-16 w-full object-contain">
                    </div>
                    <div class="rounded-xl border border-slate-700 bg-slate-950 p-4">
                        <p class="mb-3 text-xs font-semibold text-slate-300">Dark surface</p>
                        <img src="{{ $identity['logo_dark_url'] }}" alt="Preview logo dark" class="h-16 w-full object-contain">
                    </div>
                    <div class="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-4">
                        <p class="text-xs font-semibold text-[var(--foreground-muted)]">Header</p>
                        <p class="mt-3 truncate text-sm font-bold text-[var(--foreground)]">{{ $identity['application_name'] }}</p>
                        <p class="truncate text-xs text-[var(--primary)]">{{ $identity['tagline'] }}</p>
                    </div>
                    <div class="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-4 text-center">
                        <p class="text-xs font-semibold text-[var(--foreground-muted)]">Favicon</p>
                        <img src="{{ $identity['favicon_url'] }}" alt="Preview favicon" class="mx-auto mt-3 h-12 w-12 rounded-xl object-contain">
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[var(--primary)] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--focus-ring)] focus:ring-offset-2">Simpan Identitas Aplikasi</button>
            </div>
        </form>

        <section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 dark:border-amber-800 dark:bg-amber-950/30 sm:p-6">
            <h2 class="text-base font-bold text-amber-950 dark:text-amber-100">Kembalikan ke Identitas Default</h2>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">Nilai kustom dan aset upload akan dihapus. Aset default source-controlled tetap aman.</p>
            <form method="POST" action="{{ route('admin.system.application-identity.reset') }}" class="mt-4 space-y-3">
                @csrf
                <label class="flex items-start gap-3 text-sm text-amber-950 dark:text-amber-100">
                    <input type="checkbox" name="confirm_reset" value="1" x-model="resetConfirmed" class="mt-0.5 rounded border-amber-400 text-amber-700 focus:ring-amber-500">
                    <span>Saya memahami bahwa identitas dan aset branding kustom akan dihapus.</span>
                </label>
                <button type="submit" :disabled="!resetConfirmed" class="rounded-xl border border-amber-500 bg-white px-4 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-amber-950 dark:text-amber-100">Kembalikan ke Identitas Default</button>
            </form>
        </section>
    </div>
</x-app-layout>
