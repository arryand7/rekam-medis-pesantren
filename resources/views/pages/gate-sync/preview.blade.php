<x-app-layout>
    <x-slot name="title">Preview Sync Gate</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Simulasi & Preview Gate Dry-Run Sync</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Pratinjau non-mutatif klasifikasi sinkronisasi pengguna dari Gate SSO. Tidak mengubah data database utama.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('gate-sync.preview', ['run' => 1]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-[var(--surface-muted)] text-[var(--foreground)] hover:bg-[var(--border)] border border-[var(--border)] transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Jalankan Simulasi Dry-Run
                </a>

                @can('execute-gate-sync-apply')
                    @if($report)
                        <form action="{{ route('gate.sync.apply') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerapkan sinkronisasi data {{ $report['summary']['total'] ?? 0 }} pengguna dari Gate SSO ke basis data lokal?');" class="inline">
                            @csrf
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="per_page" value="{{ max(50, ($report['summary']['total'] ?? 50)) }}">
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition-colors shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Terapkan Perubahan (Apply Sync)</span>
                            </button>
                        </form>
                    @endif
                @endcan

                <a href="{{ route('gate.sync.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline">
                    Dashboard Sync &rarr;
                </a>
            </div>
        </div>

        <!-- Feedback Messages -->
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($report)
            <!-- Dry Run Summary Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                <div class="bg-[var(--surface)] p-4 rounded-xl border border-[var(--border)] text-center">
                    <div class="text-xs text-[var(--foreground-muted)] font-medium">Total Record</div>
                    <div class="text-xl font-bold text-[var(--foreground)] mt-1">{{ $report['summary']['total'] ?? 0 }}</div>
                </div>

                <div class="bg-[var(--surface)] p-4 rounded-xl border border-[var(--border)] text-center">
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Baru (New)</div>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $report['summary']['new'] ?? 0 }}</div>
                </div>

                <div class="bg-[var(--surface)] p-4 rounded-xl border border-[var(--border)] text-center">
                    <div class="text-xs text-sky-600 dark:text-sky-400 font-medium">Cocok (Matched)</div>
                    <div class="text-xl font-bold text-sky-600 dark:text-sky-400 mt-1">{{ $report['summary']['matched'] ?? 0 }}</div>
                </div>

                <div class="bg-[var(--surface)] p-4 rounded-xl border border-[var(--border)] text-center">
                    <div class="text-xs text-amber-600 dark:text-amber-400 font-medium">Berubah (Changed)</div>
                    <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $report['summary']['changed'] ?? 0 }}</div>
                </div>

                <div class="bg-[var(--surface)] p-4 rounded-xl border border-[var(--border)] text-center">
                    <div class="text-xs text-rose-600 dark:text-rose-400 font-medium">Konflik (Conflict)</div>
                    <div class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $report['summary']['conflict'] ?? 0 }}</div>
                </div>

                <div class="bg-[var(--surface)] p-4 rounded-xl border border-[var(--border)] text-center">
                    <div class="text-xs text-[var(--foreground-muted)] font-medium">Non-Aktif</div>
                    <div class="text-xl font-bold text-[var(--foreground-muted)] mt-1">{{ $report['summary']['deactivated'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
                <div class="p-4 border-b border-[var(--border)] font-bold text-sm text-[var(--foreground)]">
                    Hasil Pratinjau Klasifikasi Per Item
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[var(--foreground)]">
                        <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                            <tr>
                                <th class="px-6 py-3.5">Gate User ID</th>
                                <th class="px-6 py-3.5">Nama & Tipe</th>
                                <th class="px-6 py-3.5">Klasifikasi Dry-Run</th>
                                <th class="px-6 py-3.5">Matched Person ID</th>
                                <th class="px-6 py-3.5">Keterangan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            @foreach($report['items'] as $item)
                                <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs font-bold text-[var(--primary)]">
                                        {{ $item['gate_user_id'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-[var(--foreground)]">{{ $item['name'] }}</div>
                                        <div class="text-xs text-[var(--foreground-muted)] capitalize">{{ $item['user_type'] }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeClass = match($item['classification']) {
                                                'new' => 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300',
                                                'changed' => 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300',
                                                'conflict' => 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300',
                                                'deactivated' => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300',
                                                default => 'bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                            {{ strtoupper($item['classification']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-[var(--foreground-muted)]">
                                        {{ $item['matched_person_id'] ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                        {{ $item['reason'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-[var(--surface)] p-8 text-center rounded-2xl border border-[var(--border)] shadow-xs space-y-3">
                <p class="text-sm text-[var(--foreground-muted)]">Klik tombol di atas untuk menjalankan simulasi Dry-Run Sync dari Gate SSO Client Contract.</p>
            </div>
        @endif
    </div>
</x-app-layout>
