<x-app-layout>
    <x-slot name="title">Rekonsiliasi Identitas Gate</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('gate.sync.index') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline mb-1 inline-flex items-center gap-1">
                    &larr; Kembali ke Dashboard Sinkronisasi
                </a>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Rekonsiliasi & Resolusi Konflik Identitas</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Tinjau kandidat konflik identitas dan setujui pemetaan identitas legasi ke ID Gate resmi.
                </p>
            </div>
        </div>

        <!-- Overview Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Terpetakan ke Gate</div>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $overview['total_mapped'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Belum Terpetakan</div>
                <div class="text-2xl font-bold text-slate-600 dark:text-slate-300 mt-1">{{ $overview['total_unmapped'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-amber-500">Menunggu Tinjauan</div>
                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $overview['pending_mappings_count'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-sky-500">Disetujui</div>
                <div class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-1">{{ $overview['approved_mappings_count'] }}</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700">
            <a href="{{ route('gate.reconciliation.index', ['status' => 'pending']) }}"
               class="px-4 py-2 text-sm font-semibold border-b-2 {{ $status === 'pending' ? 'border-sky-600 text-sky-600 dark:text-sky-400' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Menunggu Persetujuan (Pending)
            </a>
            <a href="{{ route('gate.reconciliation.index', ['status' => 'approved']) }}"
               class="px-4 py-2 text-sm font-semibold border-b-2 {{ $status === 'approved' ? 'border-sky-600 text-sky-600 dark:text-sky-400' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Disetujui (Approved)
            </a>
            <a href="{{ route('gate.reconciliation.index', ['status' => 'rejected']) }}"
               class="px-4 py-2 text-sm font-semibold border-b-2 {{ $status === 'rejected' ? 'border-sky-600 text-sky-600 dark:text-sky-400' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Ditolak (Rejected)
            </a>
        </div>

        <!-- Mappings Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase border-b border-slate-200/80 dark:border-slate-700/60">
                        <tr>
                            <th class="px-6 py-3.5">Gate User ID</th>
                            <th class="px-6 py-3.5">Target Person Lokal</th>
                            <th class="px-6 py-3.5">Metode Pemetaan</th>
                            <th class="px-6 py-3.5">Skor Keyakinan</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Catatan</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-700/60">
                        @forelse ($mappings as $map)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition">
                                <td class="px-6 py-4 font-mono font-medium text-slate-900 dark:text-white">{{ $map->gate_user_id }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $map->person ? $map->person->name : '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $map->person_id }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">{{ $map->mapping_method }}</td>
                                <td class="px-6 py-4 font-semibold">{{ $map->confidence_score * 100 }}%</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $map->status === 'approved' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : ($map->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                        {{ strtoupper($map->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate">{{ $map->notes ?? '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if ($map->status === 'pending')
                                        @can('manage-identity-mappings')
                                        <div class="inline-flex items-center gap-2">
                                            <form action="{{ route('gate.reconciliation.approve', $map->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-sm transition">
                                                    Setujui
                                                </button>
                                            </form>
                                            <form action="{{ route('gate.reconciliation.reject', $map->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold shadow-sm transition">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                        @endcan
                                    @else
                                        <span class="text-xs text-slate-400">Tuntas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-400">Tidak ada data pemetaan identitas pada status ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($mappings->hasPages())
                <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-700/60">
                    {{ $mappings->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
