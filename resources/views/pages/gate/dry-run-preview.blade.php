<x-app-layout>
    <x-slot name="title">Pratinjau Sinkronisasi (Dry-Run) - Gate</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('gate.sync.index') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline mb-1 inline-flex items-center gap-1">
                    &larr; Kembali ke Dashboard Sinkronisasi
                </a>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Hasil Simulasi Sinkronisasi Gate (Dry-Run)</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Simulasi perbandingan data Gate dengan basis data lokal. Operasi ini <strong>tidak mengubah data apapun</strong>.
                </p>
            </div>

            @can('execute-gate-sync-apply')
            <form action="{{ route('gate.sync.apply') }}" method="POST" onsubmit="return confirm('Terapkan perubahan identitas ini ke basis data lokal?');">
                @csrf
                <input type="hidden" name="page" value="{{ $page }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold shadow-md shadow-sky-600/20 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Terapkan Perubahan (Apply Sync)</span>
                </button>
            </form>
            @endcan
        </div>

        <!-- Summary Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm text-center">
                <div class="text-xs text-slate-400 font-semibold uppercase">Total</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $preview['summary']['total'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm text-center">
                <div class="text-xs text-emerald-500 font-semibold uppercase">Baru</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $preview['summary']['new'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm text-center">
                <div class="text-xs text-sky-500 font-semibold uppercase">Berubah</div>
                <div class="text-xl font-bold text-sky-600 dark:text-sky-400 mt-1">{{ $preview['summary']['changed'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm text-center">
                <div class="text-xs text-slate-400 font-semibold uppercase">Identik</div>
                <div class="text-xl font-bold text-slate-600 dark:text-slate-300 mt-1">{{ $preview['summary']['unchanged'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm text-center">
                <div class="text-xs text-amber-500 font-semibold uppercase">Konflik</div>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $preview['summary']['conflict'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm text-center">
                <div class="text-xs text-rose-500 font-semibold uppercase">Nonaktif</div>
                <div class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $preview['summary']['deactivated'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm text-center">
                <div class="text-xs text-rose-400 font-semibold uppercase">Invalid</div>
                <div class="text-xl font-bold text-rose-500 mt-1">{{ $preview['summary']['invalid_payload'] }}</div>
            </div>
        </div>

        <!-- Classified Items Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60">
                <h3 class="font-bold text-slate-900 dark:text-white">Daftar Klasifikasi Identitas</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase border-b border-slate-200/80 dark:border-slate-700/60">
                        <tr>
                            <th class="px-6 py-3.5">Gate User ID</th>
                            <th class="px-6 py-3.5">Nama</th>
                            <th class="px-6 py-3.5">Tipe Pengguna</th>
                            <th class="px-6 py-3.5">Status Klasifikasi</th>
                            <th class="px-6 py-3.5">Keterangan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-700/60">
                        @foreach ($preview['items'] as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-6 py-4 font-mono text-xs font-medium text-slate-900 dark:text-white">{{ $item['gate_user_id'] }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 capitalize">{{ $item['user_type'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                        @if($item['classification'] === 'new') bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300
                                        @elseif($item['classification'] === 'changed') bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300
                                        @elseif($item['classification'] === 'conflict') bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300
                                        @elseif($item['classification'] === 'deactivated') bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300
                                        @else bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 @endif">
                                        {{ strtoupper($item['classification']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">{{ $item['reason'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
