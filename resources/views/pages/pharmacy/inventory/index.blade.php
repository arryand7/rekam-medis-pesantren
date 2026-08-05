<x-app-layout>
    <x-slot name="title">Stok & Inventaris Farmasi — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Header Banner & Action Buttons -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Stok & Batch Inventaris Farmasi</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Pemantauan persediaan batch obat, masa kedaluwarsa, dan penerimaan stok.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pharmacy.receipt.create') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700">
                    + Penerimaan Stok Obat
                </a>
                <a href="{{ route('pharmacy.adjustments.create') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-600 text-white hover:bg-amber-700">
                    Penyesuaian (Stok Opname)
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-700 dark:text-emerald-300 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Batch Inventory Table -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--border)] font-bold text-sm text-[var(--foreground)] flex items-center justify-between">
                <span>Batch Obat Aktif di Gudang/Apotek Poskestren</span>
                <span class="text-xs text-[var(--foreground-muted)] font-normal">Tracking Kedaluwarsa & Saldo Mutasi</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Nama Obat</th>
                            <th class="px-6 py-3.5">No. Batch</th>
                            <th class="px-6 py-3.5">Lokasi Stok</th>
                            <th class="px-6 py-3.5">Tgl Kedaluwarsa</th>
                            <th class="px-6 py-3.5">Stok Saat Ini</th>
                            <th class="px-6 py-3.5">Status Batch</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($batches as $b)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[var(--foreground)]">{{ $b->medicine->generic_name }}</div>
                                    <div class="text-xs text-[var(--foreground-muted)] font-mono">{{ $b->medicine->code }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono font-bold text-[var(--primary)]">
                                    {{ $b->batch_number }}
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                    {{ $b->location->name ?? 'Apotek Utama' }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($b->isExpired())
                                        <span class="font-bold text-rose-600 dark:text-rose-400">
                                            ⚠️ Kedaluwarsa ({{ $b->expiry_date->format('d M Y') }})
                                        </span>
                                    @elseif($b->isNearExpiry())
                                        <span class="font-bold text-amber-600 dark:text-amber-400">
                                            ⏳ Hampir Kedaluwarsa ({{ $b->expiry_date->format('d M Y') }})
                                        </span>
                                    @else
                                        <span class="text-[var(--foreground-muted)] font-mono">
                                            {{ $b->expiry_date ? $b->expiry_date->format('d M Y') : '-' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-sm text-[var(--foreground)]">
                                        {{ $b->current_quantity }} {{ $b->medicine->base_unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                        {{ $b->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    Belum ada batch obat yang diterima di inventaris Poskestren.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
