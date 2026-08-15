<x-app-layout>
    <x-slot name="title">Laporan: {{ ucwords(str_replace('_', ' ', $reportType)) }}</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Breadcrumb -->
        <div class="border-b border-[var(--border)] pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Pusat Laporan
                </a>
                <h1 class="text-2xl font-bold text-[var(--foreground)] capitalize">
                    Laporan: {{ str_replace('_', ' ', $reportType) }}
                </h1>
                @if($reportType === 'pharmacy_stock')
                    <p class="text-xs text-[var(--foreground-muted)] mt-0.5">Snapshot stok inventaris farmasi terkini — Real-time</p>
                @endif
            </div>

            <!-- Export Button -->
            @can('export-health-reports')
                <div>
                    <a href="{{ route('reports.export', array_merge(['report_type' => $reportType], request()->query())) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Ekspor ke CSV (Excel)
                    </a>
                </div>
            @endcan
        </div>

        <!-- Summary KPI Strip -->
        @if(isset($summary) && is_array($summary) && count($summary) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($summary as $kpiKey => $kpiValue)
                    <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">
                            {{ ucwords(str_replace('_', ' ', $kpiKey)) }}
                        </span>
                        <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1">
                            {{ $kpiValue }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Filter bar -->
        <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] p-4 shadow-xs">
            <form method="GET" action="{{ route('reports.show') }}" class="flex flex-wrap gap-4 items-end text-sm">
                <input type="hidden" name="report_type" value="{{ $reportType }}">
                @if($reportType === 'pharmacy_stock')
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-xs font-semibold uppercase text-[var(--foreground-muted)] mb-1">Cari Obat / No. Batch</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama obat atau nomor batch..." class="w-full text-xs p-2 rounded-lg border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                            Cari
                        </button>
                    </div>
                    @if(!empty($filters['search']) || !empty($filters['status']))
                        <div>
                            <a href="{{ route('reports.show', ['report_type' => $reportType]) }}" class="px-3 py-2 text-xs font-medium text-[var(--foreground-muted)] hover:underline">
                                Reset
                            </a>
                        </div>
                    @endif
                @else
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs font-semibold uppercase text-[var(--foreground-muted)] mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="w-full text-xs p-2 rounded-lg border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs font-semibold uppercase text-[var(--foreground-muted)] mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="w-full text-xs p-2 rounded-lg border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                            Terapkan Filter
                        </button>
                    </div>
                    @if(!empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filters['status']))
                        <div>
                            <a href="{{ route('reports.show', ['report_type' => $reportType]) }}" class="px-3 py-2 text-xs font-medium text-[var(--foreground-muted)] hover:underline">
                                Reset
                            </a>
                        </div>
                    @endif
                @endif
            </form>
        </div>

        <!-- Dynamic Report Table -->
        <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[var(--border)] text-sm text-left">
                    <thead class="bg-[var(--surface-muted)] text-[var(--foreground-muted)] font-semibold text-xs uppercase">
                        @if($reportType === 'visit_census')
                            <tr>
                                <th class="py-3.5 px-4">No. Kunjungan</th>
                                <th class="py-3.5 px-4">Nama Pasien</th>
                                <th class="py-3.5 px-4">Waktu Masuk</th>
                                <th class="py-3.5 px-4">Keluhan Utama</th>
                                <th class="py-3.5 px-4">Status</th>
                            </tr>
                        @elseif($reportType === 'observation_census')
                            <tr>
                                <th class="py-3.5 px-4">Episode ID</th>
                                <th class="py-3.5 px-4">Nama Pasien</th>
                                <th class="py-3.5 px-4">Waktu Mulai</th>
                                <th class="py-3.5 px-4">Waktu Selesai</th>
                                <th class="py-3.5 px-4">Status</th>
                            </tr>
                        @elseif($reportType === 'referral_census')
                            <tr>
                                <th class="py-3.5 px-4">No. Rujukan</th>
                                <th class="py-3.5 px-4">Nama Pasien</th>
                                <th class="py-3.5 px-4">Faskes Mitra</th>
                                <th class="py-3.5 px-4">Waktu Rujukan</th>
                                <th class="py-3.5 px-4">Status</th>
                            </tr>
                        @elseif($reportType === 'discharge_followup')
                            <tr>
                                <th class="py-3.5 px-4">No. Kunjungan</th>
                                <th class="py-3.5 px-4">Nama Pasien</th>
                                <th class="py-3.5 px-4">Tipe Pulang</th>
                                <th class="py-3.5 px-4">Anjuran Aktivitas</th>
                                <th class="py-3.5 px-4">Kontrol</th>
                                <th class="py-3.5 px-4">Status</th>
                            </tr>
                        @elseif($reportType === 'pharmacy_stock')
                            <tr>
                                <th class="py-3.5 px-4">Nama Obat</th>
                                <th class="py-3.5 px-4">No. Batch</th>
                                <th class="py-3.5 px-4">Lokasi</th>
                                <th class="py-3.5 px-4 text-right">Stok</th>
                                <th class="py-3.5 px-4">Kedaluwarsa</th>
                                <th class="py-3.5 px-4">Status</th>
                            </tr>
                        @else
                            <tr>
                                <th class="py-3.5 px-4">ID</th>
                                <th class="py-3.5 px-4">Tipe</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Waktu</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody class="divide-y divide-[var(--border)] text-[var(--foreground)]">
                        @forelse($data as $row)
                            <tr class="hover:bg-[var(--surface-muted)] transition">
                                @if($reportType === 'visit_census')
                                    <td class="py-3.5 px-4 font-mono font-medium text-blue-600 dark:text-blue-400">
                                        <a href="{{ route('visits.show', $row->id) }}" class="hover:underline">{{ $row->visit_number }}</a>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-[var(--foreground)]">{{ $row->patient?->person?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs text-[var(--foreground-muted)]">{{ $row->created_at?->format('d M Y H:i') }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->chief_complaint }}</td>
                                    <td class="py-3.5 px-4"><span class="px-2.5 py-1 text-xs rounded-full bg-[var(--surface-muted)] font-medium">{{ $row->status }}</span></td>
                                @elseif($reportType === 'observation_census')
                                    <td class="py-3.5 px-4 font-mono text-xs text-blue-600 dark:text-blue-400">
                                        <a href="{{ route('observations.show', $row->id) }}" class="hover:underline">{{ substr($row->id, 0, 12) }}...</a>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-[var(--foreground)]">{{ $row->medicalVisit?->patient?->person?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs text-[var(--foreground-muted)]">{{ $row->started_at?->format('d M Y H:i') }}</td>
                                    <td class="py-3.5 px-4 text-xs text-[var(--foreground-muted)]">{{ $row->ended_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="py-3.5 px-4"><span class="px-2.5 py-1 text-xs rounded-full bg-[var(--surface-muted)] font-medium">{{ $row->status }}</span></td>
                                @elseif($reportType === 'referral_census')
                                    <td class="py-3.5 px-4 font-mono font-medium text-blue-600 dark:text-blue-400">
                                        <a href="{{ route('referrals.show', $row->id) }}" class="hover:underline">{{ $row->referral_number }}</a>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-[var(--foreground)]">{{ $row->medicalVisit?->patient?->person?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 font-medium">{{ $row->healthcarePartner?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs text-[var(--foreground-muted)]">{{ $row->created_at?->format('d M Y H:i') }}</td>
                                    <td class="py-3.5 px-4"><span class="px-2.5 py-1 text-xs rounded-full bg-[var(--surface-muted)] font-medium">{{ $row->status }}</span></td>
                                @elseif($reportType === 'discharge_followup')
                                    <td class="py-3.5 px-4 font-mono font-medium text-blue-600 dark:text-blue-400">
                                        <a href="{{ route('discharges.show', $row->id) }}" class="hover:underline">{{ $row->medicalVisit?->visit_number ?? '-' }}</a>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-[var(--foreground)]">{{ $row->medicalVisit?->patient?->person?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs capitalize">{{ str_replace('_', ' ', $row->discharge_type) }}</td>
                                    <td class="py-3.5 px-4 text-xs capitalize">{{ str_replace('_', ' ', $row->activity_recommendation) }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->follow_up_required ? 'Ya ('.$row->follow_up_date?->format('d M').')' : 'Tidak' }}</td>
                                    <td class="py-3.5 px-4"><span class="px-2.5 py-1 text-xs rounded-full bg-[var(--surface-muted)] font-medium">{{ $row->status }}</span></td>
                                @elseif($reportType === 'pharmacy_stock')
                                    <td class="py-3.5 px-4 font-medium text-[var(--foreground)]">{{ $row->medicine?->brand_name ?? $row->medicine?->generic_name ?? 'Obat' }}</td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-[var(--foreground-muted)]">{{ $row->batch_number }}</td>
                                    <td class="py-3.5 px-4 text-xs text-[var(--foreground-muted)]">{{ $row->location?->name ?? 'Apotek Utama' }}</td>
                                    <td class="py-3.5 px-4 text-right font-bold {{ $row->current_quantity <= 0 ? 'text-rose-600' : '' }}">{{ $row->current_quantity }}</td>
                                    <td class="py-3.5 px-4 text-xs text-[var(--foreground-muted)]">{{ $row->expiry_date?->format('d M Y') }}</td>
                                    <td class="py-3.5 px-4">
                                        @if($row->isExpired())
                                            <span class="px-2.5 py-1 text-xs rounded-full bg-rose-500/10 text-rose-700 dark:text-rose-400 font-medium">Kedaluwarsa</span>
                                        @elseif($row->current_quantity <= 0)
                                            <span class="px-2.5 py-1 text-xs rounded-full bg-slate-500/10 text-slate-700 dark:text-slate-400 font-medium">Habis</span>
                                        @elseif($row->isNearExpiry())
                                            <span class="px-2.5 py-1 text-xs rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-400 font-medium">Hampir Kedaluwarsa</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-medium">Aktif</span>
                                        @endif
                                    </td>
                                @else
                                    <td class="py-3.5 px-4 text-xs font-mono">{{ substr($row->id, 0, 8) }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->destination ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs"><span class="px-2.5 py-1 rounded-full bg-[var(--surface-muted)]">{{ $row->result ?? '-' }}</span></td>
                                    <td class="py-3.5 px-4 text-xs text-[var(--foreground-muted)]">{{ $row->started_at?->format('d M Y H:i') ?? '-' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-[var(--foreground-muted)] text-sm">
                                    Belum ada data pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if(method_exists($data, 'links'))
                <div class="p-4 border-t border-[var(--border)] bg-[var(--surface-muted)]">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
