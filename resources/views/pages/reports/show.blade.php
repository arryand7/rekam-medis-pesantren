<x-app-layout>
    <x-slot name="title">Laporan: {{ ucwords(str_replace('_', ' ', $reportType)) }} — SABIRA POSKESTREN</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-block">
                &larr; Kembali ke Pusat Laporan
            </a>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 capitalize">
                Laporan: {{ str_replace('_', ' ', $reportType) }}
            </h1>
        </div>

        <!-- Filter bar -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <form method="GET" action="{{ route('reports.show') }}" class="flex flex-wrap gap-4 items-center text-sm">
                <input type="hidden" name="report_type" value="{{ $reportType }}">
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-semibold uppercase text-zinc-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="w-full text-xs p-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs font-semibold uppercase text-zinc-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="w-full text-xs p-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                </div>
                <div class="self-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Dynamic Report Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 font-semibold">
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
                        @elseif($reportType === 'pharmacy_stock')
                            <tr>
                                <th class="py-3.5 px-4">Nama Obat</th>
                                <th class="py-3.5 px-4">No. Batch</th>
                                <th class="py-3.5 px-4">Lokasi Stok</th>
                                <th class="py-3.5 px-4">Stok Saat Ini</th>
                                <th class="py-3.5 px-4">Tanggal Kedaluwarsa</th>
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
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-700 dark:text-zinc-300">
                        @forelse($data as $row)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition">
                                @if($reportType === 'visit_census')
                                    <td class="py-3.5 px-4 font-mono font-medium text-blue-600 dark:text-blue-400">{{ $row->visit_number }}</td>
                                    <td class="py-3.5 px-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $row->patient?->person?->full_name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->created_at?->format('d M Y H:i') }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->chief_complaint }}</td>
                                    <td class="py-3.5 px-4"><span class="px-2 py-0.5 text-xs rounded-full bg-zinc-100 dark:bg-zinc-700 font-medium">{{ $row->status }}</span></td>
                                @elseif($reportType === 'observation_census')
                                    <td class="py-3.5 px-4 font-mono text-xs">{{ substr($row->id, 0, 12) }}...</td>
                                    <td class="py-3.5 px-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $row->medicalVisit?->patient?->person?->full_name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->started_at?->format('d M Y H:i') }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->completed_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="py-3.5 px-4"><span class="px-2 py-0.5 text-xs rounded-full bg-zinc-100 dark:bg-zinc-700 font-medium">{{ $row->status }}</span></td>
                                @elseif($reportType === 'referral_census')
                                    <td class="py-3.5 px-4 font-mono font-medium text-blue-600 dark:text-blue-400">{{ $row->referral_number }}</td>
                                    <td class="py-3.5 px-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $row->medicalVisit?->patient?->person?->full_name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 font-medium">{{ $row->healthcarePartner?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->created_at?->format('d M Y H:i') }}</td>
                                    <td class="py-3.5 px-4"><span class="px-2 py-0.5 text-xs rounded-full bg-zinc-100 dark:bg-zinc-700 font-medium">{{ $row->status }}</span></td>
                                @elseif($reportType === 'pharmacy_stock')
                                    <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ $row->medicine?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 font-mono text-xs">{{ $row->batch_number }}</td>
                                    <td class="py-3.5 px-4">{{ $row->location?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 font-bold {{ $row->current_quantity <= 10 ? 'text-amber-600 dark:text-amber-400' : '' }}">{{ $row->current_quantity }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->expiry_date?->format('d M Y') }}</td>


                                @else
                                    <td class="py-3.5 px-4 font-mono text-xs">{{ substr($row->id, 0, 12) }}...</td>
                                    <td class="py-3.5 px-4">{{ $row->destination ?? '-' }}</td>
                                    <td class="py-3.5 px-4">{{ $row->result ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-xs">{{ $row->created_at?->format('d M Y H:i') }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada data untuk laporan ini pada filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($data->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $data->appends($filters)->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

