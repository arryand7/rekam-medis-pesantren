<x-app-layout>
    <x-slot name="title">Sinkronisasi Identitas Gate - POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Sinkronisasi Pengguna Gate</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola sinkronisasi proyeksi identitas person dan akun pengguna dari SABIRA Gate SSO.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('gate.sync.dry_run') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200 text-sm font-semibold shadow-sm transition">
                    <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Simulasi (Dry-Run)</span>
                </a>

                @can('execute-gate-sync-apply')
                <form action="{{ route('gate.sync.apply') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerapkan sinkronisasi identitas dari Gate? Operasi ini aman dan idempoten.');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Terapkan Sinkronisasi (Apply)</span>
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <!-- Connection Status Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Driver Gate</div>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-lg font-bold text-slate-900 dark:text-white uppercase font-mono">{{ $health['driver'] }}</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $health['reachable'] ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-rose-100 text-rose-700' }}">
                        {{ $health['reachable'] ? 'ONLINE' : 'OFFLINE' }}
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">SSO Production Flag</div>
                <div class="mt-2 text-lg font-bold font-mono text-slate-900 dark:text-white">
                    {{ config('gate.sso_enabled') ? 'ENABLED' : 'DISABLED (Sandbox)' }}
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Application Code</div>
                <div class="mt-2 text-lg font-bold font-mono text-sky-600 dark:text-sky-400">
                    {{ config('gate.app_code') }}
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aksi Rekonsiliasi</div>
                <div class="mt-2">
                    <a href="{{ route('gate.reconciliation.index') }}" class="text-sm font-semibold text-sky-600 dark:text-sky-400 hover:underline">
                        Lihat Rekonsiliasi &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Runs Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white">Riwayat Eksekusi Sinkronisasi</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase border-b border-slate-200/80 dark:border-slate-700/60">
                        <tr>
                            <th class="px-6 py-3.5">ID Eksekusi</th>
                            <th class="px-6 py-3.5">Tipe</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Total Data</th>
                            <th class="px-6 py-3.5">Diterapkan</th>
                            <th class="px-6 py-3.5">Konflik</th>
                            <th class="px-6 py-3.5">Waktu Mulai</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-700/60">
                        @forelse ($recentRuns as $run)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-6 py-4 font-mono font-medium text-slate-900 dark:text-white">{{ $run->id }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $run->run_type === 'apply' ? 'bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300' : 'bg-slate-100 text-slate-700' }}">
                                        {{ strtoupper($run->run_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $run->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : ($run->status === 'running' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                        {{ strtoupper($run->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $run->total_records }}</td>
                                <td class="px-6 py-4 text-emerald-600 font-semibold">{{ $run->applied_count }}</td>
                                <td class="px-6 py-4 text-amber-600 font-semibold">{{ $run->conflict_count }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $run->started_at->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('gate.sync.show', $run->id) }}" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline">
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-slate-400">Belum ada riwayat sinkronisasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($recentRuns->hasPages())
                <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-700/60">
                    {{ $recentRuns->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
