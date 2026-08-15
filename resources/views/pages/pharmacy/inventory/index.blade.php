<x-app-layout>
    <x-slot name="title">Stok & Inventaris Farmasi</x-slot>

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

        @php
            $hasActiveFilters = filled($filters['search'] ?? null)
                || filled($filters['condition'] ?? null)
                || filled($filters['location'] ?? null);
        @endphp

        <form method="GET" action="{{ route('pharmacy.inventory.index') }}" class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl p-4 shadow-xs" aria-label="Filter inventaris obat">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(18rem,1fr)_14rem_16rem_auto] gap-3 items-end">
                <div>
                    <label for="inventory-search" class="block text-xs font-semibold text-[var(--foreground)] mb-1.5">Cari obat atau batch</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--foreground-muted)] pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                        <input
                            id="inventory-search"
                            name="search"
                            type="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Nama, merek, kode, no. batch, atau lokasi"
                            maxlength="100"
                            class="w-full rounded-xl border border-[var(--border)] bg-[var(--surface)] pl-10 pr-3 py-2.5 text-sm text-[var(--foreground)] placeholder:text-[var(--foreground-tertiary)] focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20"
                        >
                    </div>
                </div>

                <div>
                    <label for="inventory-condition" class="block text-xs font-semibold text-[var(--foreground)] mb-1.5">Kondisi stok</label>
                    <select id="inventory-condition" name="condition" class="w-full rounded-xl border border-[var(--border)] bg-[var(--surface)] px-3 py-2.5 text-sm text-[var(--foreground)] focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20">
                        <option value="">Semua kondisi</option>
                        <option value="available" @selected(($filters['condition'] ?? '') === 'available')>Tersedia & aman</option>
                        <option value="near_expiry" @selected(($filters['condition'] ?? '') === 'near_expiry')>Hampir kedaluwarsa</option>
                        <option value="expired" @selected(($filters['condition'] ?? '') === 'expired')>Kedaluwarsa</option>
                        <option value="depleted" @selected(($filters['condition'] ?? '') === 'depleted')>Stok habis</option>
                    </select>
                </div>

                <div>
                    <label for="inventory-location" class="block text-xs font-semibold text-[var(--foreground)] mb-1.5">Lokasi stok</label>
                    <select id="inventory-location" name="location" class="w-full rounded-xl border border-[var(--border)] bg-[var(--surface)] px-3 py-2.5 text-sm text-[var(--foreground)] focus:border-[var(--primary)] focus:ring-2 focus:ring-[var(--primary)]/20">
                        <option value="">Semua lokasi</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected(($filters['location'] ?? '') === $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 xl:flex-none inline-flex items-center justify-center rounded-xl bg-[var(--primary)] px-4 py-2.5 text-sm font-bold text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-offset-2 focus:ring-offset-[var(--surface)]">
                        Terapkan
                    </button>
                    @if($hasActiveFilters)
                        <a href="{{ route('pharmacy.inventory.index') }}" class="inline-flex items-center justify-center rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] px-4 py-2.5 text-sm font-semibold text-[var(--foreground)] hover:bg-[var(--surface-subtle)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            @if($hasActiveFilters)
                <p class="mt-3 text-xs text-[var(--foreground-muted)]" role="status">
                    Ditemukan <span class="font-bold text-[var(--foreground)]">{{ $batches->total() }}</span> batch yang sesuai dengan filter.
                </p>
            @endif
        </form>

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
                                    @if($b->current_quantity <= 0)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">Stok habis</span>
                                    @elseif($b->isExpired())
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">Kedaluwarsa</span>
                                    @elseif($b->isNearExpiry())
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300">Hampir kedaluwarsa</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">{{ $b->status === 'active' ? 'Tersedia' : str_replace('_', ' ', $b->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    @if($hasActiveFilters)
                                        <div class="font-semibold text-[var(--foreground)]">Batch obat tidak ditemukan.</div>
                                        <div class="mt-1 text-xs">Coba ubah kata kunci atau reset filter inventaris.</div>
                                    @else
                                        Belum ada batch obat yang diterima di inventaris Poskestren.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($batches->hasPages())
                <div class="border-t border-[var(--border)] px-4 py-3">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
