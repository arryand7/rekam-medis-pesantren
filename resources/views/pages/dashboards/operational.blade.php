<x-app-layout>
    <x-slot name="title">Dashboard Operasional Asrama & Guru — SABIRA POSKESTREN</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span class="p-2 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                Dashboard Operasional Asrama & Guru
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Pemantauan santri yang sedang dalam anjuran istirahat, pembatasan aktivitas fisik/olahraga, atau penyesuaian KBM.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Santri Perlu Pembatasan / Istirahat Aktif</span>
                <div class="text-3xl font-extrabold text-blue-600 dark:text-blue-400 mt-2">{{ $overview['active_restrictions_count'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Sedang dalam masa pembatasan aktivitas</div>
            </div>
            <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase text-zinc-500">Notifikasi Menunggu Konfirmasi</span>
                <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">{{ $overview['pending_notifications_count'] }}</div>
                <div class="text-xs text-zinc-500 mt-1">Serah terima instruksi perawatan baru</div>
            </div>
        </div>

        <!-- Active Restrictions Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Daftar Santri dalam Pembatasan Aktivitas</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 font-semibold">
                        <tr>
                            <th class="py-3.5 px-4">Nama Santri</th>
                            <th class="py-3.5 px-4">Status Aktivitas</th>
                            <th class="py-3.5 px-4">Kategori Pembatasan</th>
                            <th class="py-3.5 px-4">Masa Berlaku</th>
                            <th class="py-3.5 px-4">Petunjuk Praktis Asrama / Guru</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-700 dark:text-zinc-300">
                        @forelse($overview['active_restrictions'] as $res)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition">
                                <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $res['person_name'] }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-medium">
                                        {{ str_replace('_', ' ', $res['activity_status']) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 capitalize font-medium">
                                    {{ str_replace('_', ' ', $res['restriction_type']) }}
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    {{ $res['effective_start'] }} s/d {{ $res['effective_until'] }}
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $res['practical_notes'] }}</div>
                                    <div class="text-zinc-500">Aktivitas diperbolehkan: {{ $res['allowed_activity'] }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada santri dalam status pembatasan aktivitas saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

