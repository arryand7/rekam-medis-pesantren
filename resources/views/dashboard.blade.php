<x-app-layout>
    <x-slot name="title">
        Dashboard Shell — SABIRA POSKESTREN Health
    </x-slot>

    <div class="space-y-6">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Dashboard Pelayanan Poskestren</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Sistem informasi rekam medis dan pemantauan kesehatan warga Pondok Pesantren SABIRA.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)]">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    Phase 0 Foundation Shell
                </span>
            </div>
        </div>

        <!-- Operational Clinical Notice Banner -->
        <div class="bg-[var(--surface-muted)] border-l-4 border-[var(--primary)] p-4 rounded-xl shadow-xs">
            <div class="flex items-start gap-3">
                <div class="p-2 rounded-lg bg-[var(--primary-soft)] text-[var(--primary)] shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-[var(--foreground)]">Prinsip Utama Pelayanan Santri Sakit</h3>
                    <p class="text-xs text-[var(--foreground-muted)] mt-1 leading-relaxed">
                        Santri yang sakit tidak boleh tetap berada di asrama dan harus segera dibawa atau diarahkan ke POSKESTREN untuk pencatatan keluhan, vital sign, assessment, serta observasi medis.
                    </p>
                </div>
            </div>
        </div>

        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Total Pasien -->
            <div class="bg-[var(--surface)] p-5 rounded-2xl border border-[var(--border)] shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-[var(--foreground-muted)] uppercase tracking-wider">Kelayakan Pasien</span>
                    <span class="p-2 rounded-xl bg-sky-100 dark:bg-sky-950 text-sky-600 dark:text-sky-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-[var(--foreground)]">Semua Warga</div>
                <p class="text-xs text-[var(--foreground-muted)]">Santri, Guru, Staf & Pengasuh</p>
            </div>

            <!-- Card 2: Kunjungan -->
            <div class="bg-[var(--surface)] p-5 rounded-2xl border border-[var(--border)] shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-[var(--foreground-muted)] uppercase tracking-wider">Kunjungan Medis</span>
                    <span class="p-2 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-[var(--foreground)]">Fondasi Ready</div>
                <p class="text-xs text-[var(--foreground-muted)]">Modul klinis disiapkan Phase 2</p>
            </div>

            <!-- Card 3: Gate SSO Sync -->
            <div class="bg-[var(--surface)] p-5 rounded-2xl border border-[var(--border)] shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-[var(--foreground-muted)] uppercase tracking-wider">Identitas SSO</span>
                    <span class="p-2 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-[var(--foreground)]">Gate SSO</div>
                <p class="text-xs text-[var(--foreground-muted)]">Person - User - Patient split</p>
            </div>

            <!-- Card 4: Konsultasi Klinis Jarak Jauh -->
            <div class="bg-[var(--surface)] p-5 rounded-2xl border border-[var(--border)] shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-[var(--foreground-muted)] uppercase tracking-wider">Konsultasi Eksternal</span>
                    <span class="p-2 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-[var(--foreground)]">Puskesmas / RS</div>
                <p class="text-xs text-[var(--foreground-muted)]">Tanpa menunda rujukan darurat</p>
            </div>
        </div>

        <!-- System Architecture Baseline Panel -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <h2 class="text-lg font-bold text-[var(--foreground)] flex items-center gap-2">
                <svg class="w-5 h-5 text-[var(--primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
                Status Fondasi Aplikasi (Phase 0 Baseline)
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-1">
                    <span class="text-xs text-[var(--foreground-muted)]">Framework & Engine</span>
                    <div class="font-semibold text-sm text-[var(--foreground)]">Laravel 13 & PHP 8.4</div>
                </div>

                <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-1">
                    <span class="text-xs text-[var(--foreground-muted)]">UI & Theme Engine</span>
                    <div class="font-semibold text-sm text-[var(--foreground)]">Livewire 4 + Tailwind CSS (Semantic Tokens)</div>
                </div>

                <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-1">
                    <span class="text-xs text-[var(--foreground-muted)]">Testing & Quality</span>
                    <div class="font-semibold text-sm text-[var(--foreground)]">Pest 4 & Larastan/PHPStan</div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
