<x-app-layout>
    <x-slot name="title">Master Data Obat</x-slot>

    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Master Data Obat (Pharmacy Directory)</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Katalog obat-obatan dan persediaan kesehatan di Poskestren.</p>
            </div>
            <div x-data="{ openAdd: false }">
                <button @click="openAdd = true" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                    + Tambah Master Obat
                </button>

                <!-- Modal Form Tambah Master Obat -->
                <div x-show="openAdd" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" x-cloak>
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] max-w-md w-full space-y-4 shadow-xl">
                        <h3 class="text-lg font-bold text-[var(--foreground)]">Pendaftaran Master Obat Baru</h3>

                        <form action="{{ route('pharmacy.medicines.store') }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Kode Obat (SKU) <span class="text-rose-500">*</span></label>
                                <input type="text" name="code" required placeholder="Contoh: MED-PCT-500" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Nama Generik <span class="text-rose-500">*</span></label>
                                <input type="text" name="generic_name" required placeholder="Contoh: Paracetamol" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Nama Paten / Merek</label>
                                <input type="text" name="brand_name" placeholder="Contoh: Sanamol / Bodrex" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Bentuk Sediaan <span class="text-rose-500">*</span></label>
                                    <select name="dosage_form" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                        <option value="tablet">Tablet</option>
                                        <option value="capsule">Kapsul</option>
                                        <option value="syrup">Sirup</option>
                                        <option value="suspension">Suspensi</option>
                                        <option value="cream">Krim / Salep</option>
                                        <option value="drops">Tetes</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Satuan Dasar <span class="text-rose-500">*</span></label>
                                    <input type="text" name="base_unit" required placeholder="tablet / botol / tube" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Kekuatan Sediaan</label>
                                    <input type="text" name="strength_text" placeholder="Contoh: 500 mg" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Stok Minimum <span class="text-rose-500">*</span></label>
                                    <input type="number" name="minimum_stock" required value="10" min="1" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="openAdd = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[var(--primary)] text-white">
                                    Simpan Master Obat
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-700 dark:text-emerald-300 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Medicines Directory Table -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Kode & Nama Obat</th>
                            <th class="px-6 py-3.5">Sediaan & Kekuatan</th>
                            <th class="px-6 py-3.5">Satuan</th>
                            <th class="px-6 py-3.5">Total Stok</th>
                            <th class="px-6 py-3.5">Stok Min</th>
                            <th class="px-6 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($medicines as $med)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[var(--foreground)]">{{ $med->generic_name }}</div>
                                    @if($med->brand_name)
                                        <div class="text-xs text-[var(--foreground-muted)]">Merek: {{ $med->brand_name }}</div>
                                    @endif
                                    <div class="text-[11px] text-[var(--foreground-muted)] font-mono">{{ $med->code }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium">
                                    {{ ucfirst($med->dosage_form) }} {{ $med->strength_text ? '('.$med->strength_text.')' : '' }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-[var(--foreground-muted)]">
                                    {{ $med->base_unit }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-sm {{ $med->isLowStock() ? 'text-rose-600 dark:text-rose-400' : 'text-[var(--foreground)]' }}">
                                        {{ $med->total_stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                    {{ $med->minimum_stock }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($med->isLowStock())
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">
                                            ⚠️ Low Stock
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                            Aman
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    Belum ada master data obat terdaftar di sistem Poskestren.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
