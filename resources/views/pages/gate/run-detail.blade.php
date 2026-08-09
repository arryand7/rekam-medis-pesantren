<x-app-layout>
    <x-slot name="title">Rincian Eksekusi Sinkronisasi - Gate</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('gate.sync.index') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline mb-1 inline-flex items-center gap-1">
                &larr; Kembali ke Dashboard Sinkronisasi
            </a>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Rincian Eksekusi Sinkronisasi: {{ $run->id }}</h1>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Status Eksekusi</h3>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Tipe Run:</span>
                    <span class="font-semibold uppercase font-mono">{{ $run->run_type }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Status:</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $run->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 text-amber-700' }}">
                        {{ strtoupper($run->status) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Waktu Mulai:</span>
                    <span>{{ $run->started_at->format('d M Y, H:i:s') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Waktu Selesai:</span>
                    <span>{{ $run->completed_at ? $run->completed_at->format('d M Y, H:i:s') : '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Operator:</span>
                    <span class="font-medium">{{ $run->executedBy ? $run->executedBy->name : 'Sistem' }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm space-y-4 md:col-span-2">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Ringkasan Statistik</h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/60">
                        <div class="text-xs text-slate-400 font-semibold uppercase">Total Data</div>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $run->total_records }}</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                        <div class="text-xs text-emerald-600 font-semibold uppercase">Diterapkan</div>
                        <div class="text-xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">{{ $run->applied_count }}</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                        <div class="text-xs text-amber-600 font-semibold uppercase">Konflik</div>
                        <div class="text-xl font-bold text-amber-700 dark:text-amber-300 mt-1">{{ $run->conflict_count }}</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800">
                        <div class="text-xs text-rose-600 font-semibold uppercase">Gagal</div>
                        <div class="text-xl font-bold text-rose-700 dark:text-rose-300 mt-1">{{ $run->failed_count }}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="text-xs font-semibold uppercase text-slate-400 mb-2">Payload Ringkasan (JSON)</h4>
                    <pre class="p-4 rounded-xl bg-slate-900 text-slate-100 text-xs font-mono overflow-x-auto">{{ json_encode($run->summary_json, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
