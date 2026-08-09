<x-app-layout>
    <x-slot name="title">Dashboard Manajemen Eksekutif — SABIRA POSKESTREN</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span class="p-2 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </span>
                Dashboard Manajemen Eksekutif
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Ringkasan statistik agregat kesehatan santri/warga pesantren (Periode: {{ $metrics['period']['start'] }} s/d {{ $metrics['period']['end'] }}).
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Total Kunjungan Medis</span>
                <div class="text-4xl font-extrabold text-blue-600 dark:text-blue-400 mt-2">{{ $metrics['total_visits'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Total interaksi poskestren pada periode ini</div>
            </div>

            <div class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Observasi Rawat Istirahat</span>
                <div class="text-4xl font-extrabold text-purple-600 dark:text-purple-400 mt-2">{{ $metrics['total_observations'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Episode pemantauan rawat istirahat poskestren</div>
            </div>

            <div class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Rujukan Faskes Luar</span>
                <div class="text-4xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-2">{{ $metrics['total_referrals'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Rujukan ke Puskesmas / RS Mitra</div>
            </div>

            <div class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Tingkat Penyelesaian Kontrol</span>
                <div class="text-4xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">{{ $metrics['follow_up_completion_rate'] }}%</div>
                <div class="text-xs text-zinc-500 mt-1">Kepatuhan jadwal kontrol pasca kepulangan</div>
            </div>

            <div class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Penyelesaian Kepulangan</span>
                <div class="text-4xl font-extrabold text-teal-600 dark:text-teal-400 mt-2">{{ $metrics['total_discharges'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Kunjungan yang telah selesai ditutup</div>
            </div>

            <div class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Stok Obat Menipis (&le; 10)</span>
                <div class="text-4xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">{{ $metrics['low_stock_medicines_count'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Item obat memerlukan pengadaan ulang</div>
            </div>
        </div>

        <div class="p-4 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-xl text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><strong>Prinsip Privasi Manajemen:</strong> Dashboard manajemen hanya menampilkan data numerik agregat dan tidak mengekspos diagnosis, rekam medis individu, ataupun riwayat obat santri.</span>
        </div>
    </div>
</x-app-layout>

