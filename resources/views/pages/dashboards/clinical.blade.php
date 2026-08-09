<x-app-layout>
    <x-slot name="title">Dashboard Klinis — SABIRA POSKESTREN</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <span class="p-2 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                    Dashboard Klinis POSKESTREN
                </h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Pemantauan alur kerja pelayanan kesehatan santri dan warga pesantren hari ini.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('visits.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    + Pendaftaran Kunjungan
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Kunjungan Hari Ini</span>
                <div class="text-3xl font-extrabold text-blue-600 dark:text-blue-400 mt-2">{{ $metrics['visits_today'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Total santri/pasien masuk</div>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Menunggu Pengkajian</span>
                <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">{{ $metrics['waiting_assessment'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Antrean pemeriksaan dokter</div>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Sedang Diobservasi</span>
                <div class="text-3xl font-extrabold text-purple-600 dark:text-purple-400 mt-2">{{ $metrics['under_observation'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Rawat istirahat di ruang POSKESTREN</div>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Rujukan Aktif</span>
                <div class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-2">{{ $metrics['referral_external'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Sedang di faskes mitra/RS</div>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Kontrol / Follow-Up Due</span>
                <div class="text-3xl font-extrabold text-orange-600 dark:text-orange-400 mt-2">{{ $metrics['follow_up_due'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Jadwal kontrol ulang hari ini</div>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Kepulangan Hari Ini</span>
                <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">{{ $metrics['discharges_today'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Kunjungan selesai dan ditutup</div>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Instruksi Obat Aktif</span>
                <div class="text-3xl font-extrabold text-teal-600 dark:text-teal-400 mt-2">{{ $metrics['pending_medications'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Order obat sedang berjalan</div>
            </div>

            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Kendala Integrasi</span>
                <div class="text-3xl font-extrabold text-rose-600 dark:text-rose-400 mt-2">{{ $metrics['integration_failures'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Outbox failed / dead-letter</div>
            </div>
        </div>
    </div>
</x-app-layout>

