<x-app-layout>
    <x-slot name="title">Dashboard Manajemen Eksekutif — SABIRA POSKESTREN</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header & Date Range Filter Toolbar -->
        <div class="border-b border-[var(--border)] pb-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] flex items-center gap-3">
                    <span class="p-2.5 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-xl shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    </span>
                    Dashboard Manajemen Eksekutif
                </h1>
                <p class="mt-1.5 text-sm text-[var(--foreground-muted)]">
                    Statistik agregat operasional kesehatan, tren kunjungan, evaluasi rujukan, dan kepatuhan kontrol santri.
                </p>
            </div>

            <!-- Date Range Filter Form -->
            <form method="GET" action="{{ route('dashboards.management') }}" class="flex flex-wrap items-center gap-2 bg-[var(--surface)] p-2 rounded-2xl border border-[var(--border)] shadow-xs">
                <div class="flex items-center gap-1">
                    <a href="{{ route('dashboards.management', ['preset' => 'today']) }}" class="px-2.5 py-1.5 text-xs font-medium rounded-xl transition {{ $preset === 'today' ? 'bg-blue-600 text-white shadow-xs' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">Hari Ini</a>
                    <a href="{{ route('dashboards.management', ['preset' => '7_days']) }}" class="px-2.5 py-1.5 text-xs font-medium rounded-xl transition {{ $preset === '7_days' ? 'bg-blue-600 text-white shadow-xs' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">7 Hari</a>
                    <a href="{{ route('dashboards.management', ['preset' => '30_days']) }}" class="px-2.5 py-1.5 text-xs font-medium rounded-xl transition {{ $preset === '30_days' ? 'bg-blue-600 text-white shadow-xs' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">30 Hari</a>
                    <a href="{{ route('dashboards.management', ['preset' => 'this_month']) }}" class="px-2.5 py-1.5 text-xs font-medium rounded-xl transition {{ $preset === 'this_month' ? 'bg-blue-600 text-white shadow-xs' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">Bulan Ini</a>
                </div>

                <div class="h-4 w-px bg-[var(--border)] mx-1 hidden sm:block"></div>

                <input type="hidden" name="preset" value="custom">
                <div class="flex items-center gap-1.5 text-xs">
                    <input type="date" name="from" value="{{ $fromInput ?? $metrics['period']['start_raw'] }}" class="px-2 py-1 bg-[var(--surface)] border border-[var(--border)] rounded-lg text-xs text-[var(--foreground)] focus:ring-1 focus:ring-blue-500">
                    <span class="text-[var(--foreground-muted)]">&ndash;</span>
                    <input type="date" name="to" value="{{ $toInput ?? $metrics['period']['end_raw'] }}" class="px-2 py-1 bg-[var(--surface)] border border-[var(--border)] rounded-lg text-xs text-[var(--foreground)] focus:ring-1 focus:ring-blue-500">
                    <button type="submit" class="px-3 py-1 bg-zinc-800 hover:bg-zinc-900 text-white dark:bg-zinc-700 dark:hover:bg-zinc-600 rounded-lg font-medium transition">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Period Banner -->
        <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/50 p-3.5 rounded-2xl text-xs text-blue-800 dark:text-blue-300">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Menampilkan data periode: <strong>{{ $metrics['period']['start'] }}</strong> s/d <strong>{{ $metrics['period']['end'] }}</strong> ({{ (int) $metrics['period']['days'] }} Hari)</span>
            </div>
            <a href="{{ route('reports.index') }}" class="underline font-semibold hover:text-blue-900 dark:hover:text-blue-200">
                Buka Semua Laporan &rarr;
            </a>
        </div>

        <!-- Aggregate KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Total Visits -->
            <div class="p-5 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Total Kunjungan Medis</span>
                    <div class="text-3xl font-extrabold text-blue-600 dark:text-blue-400 mt-2">{{ $metrics['total_visits'] }}</div>
                </div>
                <div class="mt-3 pt-3 border-t border-[var(--border)] text-xs">
                    @if($metrics['visits_comparison']['has_comparison'])
                        <span class="font-semibold {{ $metrics['visits_comparison']['direction'] === 'up' ? 'text-emerald-600' : ($metrics['visits_comparison']['direction'] === 'down' ? 'text-rose-600' : 'text-[var(--foreground-muted)]') }}">
                            {{ $metrics['visits_comparison']['label'] }}
                        </span>
                    @else
                        <span class="text-[var(--foreground-muted)] italic">{{ $metrics['visits_comparison']['label'] }}</span>
                    @endif
                </div>
            </div>

            <!-- Unique Patients -->
            <div class="p-5 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Pasien Unik Dilayani</span>
                    <div class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-2">{{ $metrics['unique_patients'] }}</div>
                </div>
                <div class="mt-3 pt-3 border-t border-[var(--border)] text-xs text-[var(--foreground-muted)]">
                    Individu santri / pengurus poskestren
                </div>
            </div>

            <!-- Observations & Referrals -->
            <div class="p-5 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Observasi vs Rujukan RS</span>
                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-3xl font-extrabold text-purple-600 dark:text-purple-400">{{ $metrics['total_observations'] }}</span>
                        <span class="text-xs text-[var(--foreground-muted)]">obs /</span>
                        <span class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $metrics['total_referrals'] }}</span>
                        <span class="text-xs text-[var(--foreground-muted)]">rujukan</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[var(--border)] text-xs text-[var(--foreground-muted)]">
                    Episode rawat & rujukan faskes luar
                </div>
            </div>

            <!-- Follow-up Completion Rate -->
            <div class="p-5 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Kepatuhan Kontrol / Follow-Up</span>
                    <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                        @if($metrics['follow_up_metrics']['has_data'])
                            {{ $metrics['follow_up_metrics']['rate'] }}%
                        @else
                            <span class="text-lg font-semibold text-[var(--foreground-muted)]">Belum ada data</span>
                        @endif
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-[var(--border)] text-xs text-[var(--foreground-muted)]">
                    @if($metrics['follow_up_metrics']['has_data'])
                        {{ $metrics['follow_up_metrics']['completed'] }} dari {{ $metrics['follow_up_metrics']['total'] }} jadwal selesai
                    @else
                        Tidak ada jadwal kontrol pada periode ini
                    @endif
                </div>
            </div>

        </div>

        <!-- Accessible Trend Visualizations & Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Trend Bars: Visits Over Time (2 cols) -->
            <div class="lg:col-span-2 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[var(--foreground)]">Tren Volume Kunjungan & Observasi</h2>
                        <p class="text-xs text-[var(--foreground-muted)]">Distribusi volume aktivitas pelayanan poskestren sepanjang periode</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-medium">
                            <span class="w-3 h-3 rounded bg-blue-500"></span> Kunjungan
                        </span>
                        <span class="flex items-center gap-1.5 text-purple-600 dark:text-purple-400 font-medium">
                            <span class="w-3 h-3 rounded bg-purple-500"></span> Observasi
                        </span>
                    </div>
                </div>

                <!-- Lightweight Accessible CSS/SVG Bar Visualization -->
                <div class="pt-4 pb-2">
                    @php
                        $maxVisits = max(1, $metrics['daily_trends']->max('visits'));
                    @endphp
                    <div class="flex items-end gap-1.5 h-44 border-b border-[var(--border)] pb-1 overflow-x-auto">
                        @foreach($metrics['daily_trends'] as $day)
                            @php
                                $heightPct = round(($day['visits'] / $maxVisits) * 100);
                            @endphp
                            <div class="flex-1 min-w-[28px] flex flex-col items-center gap-1 group relative">
                                <!-- Tooltip -->
                                <div class="absolute -top-10 hidden group-hover:flex flex-col items-center z-10 bg-zinc-900 text-white text-[10px] py-1 px-2 rounded shadow whitespace-nowrap">
                                    <span>{{ $day['label'] }}: {{ $day['visits'] }} kunjungan, {{ $day['observations'] }} obs</span>
                                </div>
                                <!-- Bar -->
                                <div class="w-full bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 rounded-t transition" style="height: {{ max(4, $heightPct) }}%"></div>
                                <!-- X-axis label -->
                                <span class="text-[9px] text-[var(--foreground-muted)] truncate w-full text-center mt-1">{{ $day['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Accessible Tabular Fallback for Screen Readers & Clarity -->
                <details class="text-xs text-[var(--foreground-muted)] cursor-pointer">
                    <summary class="font-medium hover:text-[var(--foreground)]">Tampilkan Rincian Data Tabel Tren (Aksesibilitas)</summary>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full divide-y divide-[var(--border)] text-xs text-left">
                            <thead class="font-semibold text-[var(--foreground)]">
                                <tr>
                                    <th class="py-1.5 px-2">Tanggal</th>
                                    <th class="py-1.5 px-2 text-right">Kunjungan</th>
                                    <th class="py-1.5 px-2 text-right">Observasi</th>
                                    <th class="py-1.5 px-2 text-right">Rujukan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                @foreach($metrics['daily_trends'] as $day)
                                    <tr>
                                        <td class="py-1 px-2">{{ $day['label'] }}</td>
                                        <td class="py-1 px-2 text-right font-medium text-[var(--foreground)]">{{ $day['visits'] }}</td>
                                        <td class="py-1 px-2 text-right">{{ $day['observations'] }}</td>
                                        <td class="py-1 px-2 text-right">{{ $day['referrals'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>

            <!-- Health Status Farmasi & Mutasi (1 col) -->
            <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs p-5 space-y-4 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold text-[var(--foreground)]">Status Kesehatan Stok Farmasi</h2>
                    <p class="text-xs text-[var(--foreground-muted)]">Kondisi kelayakan batch obat aktif saat ini</p>
                </div>

                <div class="space-y-3 my-auto">
                    <div class="flex items-center justify-between p-3 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                        <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-300">Batch Aktif & Aman</span>
                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ $metrics['batch_health']['active'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-amber-500/10 rounded-xl border border-amber-500/20">
                        <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">Near-Expiry (&le; 30 Hari)</span>
                        <span class="text-sm font-bold text-amber-700 dark:text-amber-400">{{ $metrics['batch_health']['near_expiry'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-rose-500/10 rounded-xl border border-rose-500/20">
                        <span class="text-xs font-semibold text-rose-800 dark:text-rose-300">Kedaluwarsa</span>
                        <span class="text-sm font-bold text-rose-700 dark:text-rose-400">{{ $metrics['batch_health']['expired'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-[var(--surface-muted)] rounded-xl border border-[var(--border)]">
                        <span class="text-xs font-semibold text-[var(--foreground)]">Batch Habis (Depleted)</span>
                        <span class="text-sm font-bold text-[var(--foreground)]">{{ $metrics['batch_health']['depleted'] }}</span>
                    </div>
                </div>

                <div class="text-xs text-[var(--foreground-muted)] pt-2 border-t border-[var(--border)] flex justify-between items-center">
                    <span>Total Mutasi Periode Ini:</span>
                    <strong class="text-[var(--foreground)]">{{ $metrics['pharmacy_movements'] }} Transaksi</strong>
                </div>
            </div>

        </div>

        <!-- Strict Privacy Guarantee Reminder Banner -->
        <div class="p-4 bg-[var(--surface-muted)] border border-[var(--border)] rounded-2xl text-xs text-[var(--foreground-muted)] flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span><strong>Prinsip Privasi Manajemen Eksekutif:</strong> Dashboard ini dirancang khusus untuk telaah manajerial berbasis angka statistik agregat. Sistem tidak menampilkan nama pasien, Nomor Rekam Medis (RM), Nomor Induk Santri (NIS), catatan SOAP medis, ataupun riwayat resep individual.</span>
        </div>

    </div>
</x-app-layout>
