<x-app-layout>
    <x-slot name="title">Dashboard Farmasi & Obat — SABIRA POSKESTREN</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="border-b border-[var(--border)] pb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] flex items-center gap-3">
                    <span class="p-2.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-xl shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </span>
                    Dashboard Farmasi & Inventaris Obat
                </h1>
                <p class="mt-1.5 text-sm text-[var(--foreground-muted)]">
                    Pemantauan masa kedaluwarsa batch, stok kritis, buku besar mutasi obat (*append-only*), dan dispensa harian.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('pharmacy.inventory.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Penerimaan / Kelola Stok
                </a>
                <a href="{{ route('reports.show', ['report_type' => 'pharmacy_stock']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[var(--surface)] border border-[var(--border)] text-[var(--foreground)] hover:bg-[var(--surface-muted)] text-sm font-medium rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Laporan Lengkap
                </a>
            </div>
        </div>

        <!-- KPI Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-rose-200 dark:border-rose-900/60 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">Kedaluwarsa</span>
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1.5">{{ $metrics['expired_batches'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Batch perlu karantina</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-amber-200 dark:border-amber-900/60 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Near-Expiry (&le; {{ $metrics['warning_days_window'] }} Hari)</span>
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1.5">{{ $metrics['near_expiry_batches'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Prioritaskan penggunaan (FEFO)</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Batch Habis</span>
                <div class="text-2xl font-extrabold text-[var(--foreground)] mt-1.5">{{ $metrics['depleted_batches'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Stok batch = 0</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Stok Obat Menipis</span>
                @if($metrics['low_stock_configured'])
                    <div class="text-2xl font-extrabold text-orange-600 dark:text-orange-400 mt-1.5">{{ $metrics['low_stock_medicines'] }}</div>
                    <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">&le; {{ $metrics['low_stock_threshold'] }} unit total</div>
                @else
                    <div class="text-sm font-semibold text-[var(--foreground-muted)] mt-2 italic">Belum Dikonfigurasi</div>
                    <div class="text-[10px] text-[var(--foreground-muted)] mt-0.5">[PERLU DIKONFIRMASI]</div>
                @endif
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Dispensa Hari Ini</span>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1.5">{{ $metrics['dispenses_today'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Pemberian ke pasien</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Mutasi Hari Ini</span>
                <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1.5">{{ $metrics['movements_today'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Log buku besar farmasi</div>
            </div>
        </div>

        <!-- Tables Layout (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Expiring Batches -->
            <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden flex flex-col">
                <div class="p-4 border-b border-[var(--border)] bg-amber-500/10 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[var(--foreground)] flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Batch Kedaluwarsa & Hampir Kedaluwarsa
                    </h2>
                    <span class="text-xs text-[var(--foreground-muted)]">Jendela {{ $metrics['warning_days_window'] }} Hari</span>
                </div>
                <div class="p-4 flex-1 overflow-x-auto">
                    @if($expiringBatches->isNotEmpty())
                        <table class="min-w-full divide-y divide-[var(--border)] text-xs text-left">
                            <thead class="font-semibold text-[var(--foreground-muted)] uppercase">
                                <tr>
                                    <th class="py-2 px-2">Obat & Batch</th>
                                    <th class="py-2 px-2">Lokasi</th>
                                    <th class="py-2 px-2">Sisa</th>
                                    <th class="py-2 px-2">Kedaluwarsa</th>
                                    <th class="py-2 px-2 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                @foreach($expiringBatches as $b)
                                    <tr class="hover:bg-[var(--surface-muted)] transition">
                                        <td class="py-2.5 px-2 font-medium text-[var(--foreground)]">
                                            {{ $b['medicine_name'] }}
                                            <div class="text-[11px] text-[var(--foreground-muted)] font-mono">B: {{ $b['batch_number'] }}</div>
                                        </td>
                                        <td class="py-2.5 px-2 text-[var(--foreground-muted)]">{{ $b['location'] }}</td>
                                        <td class="py-2.5 px-2 font-bold text-[var(--foreground)]">{{ $b['current_quantity'] }}</td>
                                        <td class="py-2.5 px-2 text-[var(--foreground)]">{{ $b['expiry_date'] }}</td>
                                        <td class="py-2.5 px-2 text-right">
                                            @if($b['is_expired'])
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                    EXPIRED
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                    {{ $b['days_remaining'] }} hari lagi
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="py-8 text-center text-xs text-[var(--foreground-muted)]">
                            Semua batch obat aman dan berada di luar jendela peringatan kedaluwarsa.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Depleted Medicines -->
            <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden flex flex-col">
                <div class="p-4 border-b border-[var(--border)] bg-[var(--surface-muted)] flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[var(--foreground)] flex items-center gap-2">
                        <svg class="w-4 h-4 text-[var(--foreground-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        Daftar Batch Habis (Depleted)
                    </h2>
                    <span class="text-xs text-[var(--foreground-muted)]">{{ $depletedMedicines->count() }} Batch</span>
                </div>
                <div class="p-4 flex-1 overflow-x-auto">
                    @if($depletedMedicines->isNotEmpty())
                        <table class="min-w-full divide-y divide-[var(--border)] text-xs text-left">
                            <thead class="font-semibold text-[var(--foreground-muted)] uppercase">
                                <tr>
                                    <th class="py-2 px-2">Nama Obat</th>
                                    <th class="py-2 px-2">Nomor Batch</th>
                                    <th class="py-2 px-2">Lokasi Terakhir</th>
                                    <th class="py-2 px-2 text-right">Waktu Habis</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                @foreach($depletedMedicines as $dm)
                                    <tr class="hover:bg-[var(--surface-muted)] transition">
                                        <td class="py-2.5 px-2 font-medium text-[var(--foreground)]">{{ $dm['medicine_name'] }}</td>
                                        <td class="py-2.5 px-2 font-mono text-[var(--foreground-muted)]">{{ $dm['batch_number'] }}</td>
                                        <td class="py-2.5 px-2 text-[var(--foreground-muted)]">{{ $dm['location'] }}</td>
                                        <td class="py-2.5 px-2 text-right text-[var(--foreground-muted)]">{{ $dm['last_updated'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="py-8 text-center text-xs text-[var(--foreground-muted)]">
                            Tidak ada batch obat yang berstatus habis.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Recent Stock Movement Ledger Snippet (Full Width) -->
        <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden">
            <div class="p-4 border-b border-[var(--border)] bg-[var(--surface-muted)] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                    <h2 class="text-sm font-bold text-[var(--foreground)]">Riwayat Mutasi Buku Besar Farmasi (*Append-Only*)</h2>
                </div>
                <span class="text-xs text-[var(--foreground-muted)]">15 Transaksi Terkini</span>
            </div>
            <div class="p-4">
                @if($recentMovements->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[var(--border)] text-xs text-left">
                            <thead class="font-semibold text-[var(--foreground-muted)] uppercase bg-[var(--surface-muted)]">
                                <tr>
                                    <th class="py-2.5 px-3">Waktu</th>
                                    <th class="py-2.5 px-3">Obat & Batch</th>
                                    <th class="py-2.5 px-3">Jenis Mutasi</th>
                                    <th class="py-2.5 px-3 text-right">Perubahan</th>
                                    <th class="py-2.5 px-3 text-right">Saldo Akhir</th>
                                    <th class="py-2.5 px-3">Petugas</th>
                                    <th class="py-2.5 px-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                @foreach($recentMovements as $mov)
                                    <tr class="hover:bg-[var(--surface-muted)] transition">
                                        <td class="py-2.5 px-3 text-[var(--foreground-muted)]">{{ $mov['created_at'] }}</td>
                                        <td class="py-2.5 px-3 font-medium text-[var(--foreground)]">
                                            {{ $mov['medicine_name'] }}
                                            <span class="text-[var(--foreground-muted)] font-mono font-normal">({{ $mov['batch_number'] }})</span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded uppercase {{ $mov['movement_type'] === 'receive' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($mov['movement_type'] === 'dispense' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300') }}">
                                                {{ $mov['movement_type'] }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono font-bold {{ $mov['quantity_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $mov['quantity_change'] >= 0 ? '+'.$mov['quantity_change'] : $mov['quantity_change'] }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-mono font-bold text-[var(--foreground)]">
                                            {{ $mov['balance_after'] }}
                                        </td>
                                        <td class="py-2.5 px-3 text-[var(--foreground)]">{{ $mov['actor_name'] }}</td>
                                        <td class="py-2.5 px-3 text-[var(--foreground-muted)] truncate max-w-xs">{{ $mov['notes'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center text-xs text-[var(--foreground-muted)]">
                        Belum ada mutasi buku besar farmasi yang tercatat.
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
