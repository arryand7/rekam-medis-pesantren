<x-app-layout>
    <x-slot name="title">Status Integrasi Absensi</x-slot>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <a href="{{ route('integration.outbox.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-block">
                &larr; Kembali ke Monitor Outbox
            </a>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                Status Konektor SABIRA Absensi
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Pemeriksaan status konektor dan konfigurasi antarmuka pengiriman disposisi kehadiran santri/staf.
            </p>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-700">
                <div>
                    <span class="text-xs font-semibold uppercase text-zinc-500">Status Konektor Driver</span>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 capitalize">{{ $probe['driver'] }} Sandbox</h2>
                </div>
                <div>
                    @if($probe['reachable'])
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-full text-xs font-semibold">
                            ONLINE / READY
                        </span>
                    @else
                        <span class="px-3 py-1 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 rounded-full text-xs font-semibold">
                            OFFLINE
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="p-3 bg-zinc-50 dark:bg-zinc-900/40 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <span class="text-xs text-zinc-500 block">Production Transport Status</span>
                    <span class="font-bold text-amber-600 dark:text-amber-400">
                        {{ $config['enabled'] ? 'ACTIVE' : 'DISABLED (Protected Sandbox Mode)' }}
                    </span>
                </div>
                <div class="p-3 bg-zinc-50 dark:bg-zinc-900/40 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <span class="text-xs text-zinc-500 block">Max Retry Attempts</span>
                    <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $config['max_retry_attempts'] }} Kali</span>
                </div>
                <div class="p-3 bg-zinc-50 dark:bg-zinc-900/40 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <span class="text-xs text-zinc-500 block">Base Retry Backoff</span>
                    <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $config['retry_backoff_seconds'] }} Detik</span>
                </div>
                <div class="p-3 bg-zinc-50 dark:bg-zinc-900/40 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <span class="text-xs text-zinc-500 block">Diagnostic Message</span>
                    <span class="text-xs text-zinc-700 dark:text-zinc-300">{{ $probe['message'] }}</span>
                </div>
            </div>

            <div class="p-4 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg text-xs text-blue-800 dark:text-blue-300">
                <strong>Catatan Keamanan & Tata Kelola:</strong> Sesuai SOP Phase 3C2, konektor ke SABIRA Absensi berjalan dalam mode in-memory sandbox. Pengaktifan transportasi HTTP production memerlukan persetujuan eksplisit dan otorisasi kunci API pada fase deployment mendatang.
            </div>
        </div>
    </div>
</x-app-layout>

