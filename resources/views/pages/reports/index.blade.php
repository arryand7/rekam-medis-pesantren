<x-app-layout>
    <x-slot name="title">Pusat Laporan Kesehatan — SABIRA POSKESTREN</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span class="p-2 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                Pusat Laporan & Sensus Kesehatan
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Akses laporan terstruktur, sensus pelayanan poskestren, dan pemantauan distribusi farmasi.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Report 1: Sensus Kunjungan -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col justify-between shadow-sm hover:border-blue-300 dark:hover:border-blue-700 transition">
                <div class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Sensus Kunjungan Medis</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Rekapitulasi riwayat kunjungan santri dan warga pesantren berdasarkan rentang tanggal dan status pelayanan.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ route('reports.show', ['report_type' => 'visit_census']) }}" class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Laporan &rarr;
                    </a>
                </div>
            </div>

            <!-- Report 2: Sensus Observasi -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col justify-between shadow-sm hover:border-blue-300 dark:hover:border-blue-700 transition">
                <div class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Sensus Observasi Poskestren</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Laporan santri yang menjalani rawat istirahat/pemantauan tanda vital berkala di ruang observasi poskestren.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ route('reports.show', ['report_type' => 'observation_census']) }}" class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Laporan &rarr;
                    </a>
                </div>
            </div>

            <!-- Report 3: Sensus Rujukan -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col justify-between shadow-sm hover:border-blue-300 dark:hover:border-blue-700 transition">
                <div class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Sensus Rujukan Eksternal</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Rekapitulasi rujukan santri ke fasilitas kesehatan mitra (Puskesmas/RS) beserta status kepulangan dan review.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ route('reports.show', ['report_type' => 'referral_census']) }}" class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Laporan &rarr;
                    </a>
                </div>
            </div>

            <!-- Report 4: Kepulangan & Follow-Up -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col justify-between shadow-sm hover:border-blue-300 dark:hover:border-blue-700 transition">
                <div class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Laporan Kepulangan & Kontrol</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Daftar kepulangan klinis santri beserta status penyelesaian rencana tindak lanjut / kontrol ulang.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ route('reports.show', ['report_type' => 'discharge_followup']) }}" class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Laporan &rarr;
                    </a>
                </div>
            </div>

            <!-- Report 5: Stok Farmasi -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col justify-between shadow-sm hover:border-blue-300 dark:hover:border-blue-700 transition">
                <div class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Laporan Inventaris Obat & Kedaluwarsa</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Pemantauan stok per batch obat, peringatan batas minimum stok, dan urutan masa kedaluwarsa.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ route('reports.show', ['report_type' => 'pharmacy_stock']) }}" class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Laporan &rarr;
                    </a>
                </div>
            </div>

            <!-- Report 6: Pengiriman Integrasi -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col justify-between shadow-sm hover:border-blue-300 dark:hover:border-blue-700 transition">
                <div class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Laporan Delivery Outbox Integrasi</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Audit riwayat pengiriman event integrasi ke aplikasi Absensi dan sistem operasional lainnya.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="{{ route('reports.show', ['report_type' => 'integration_delivery']) }}" class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Laporan &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

