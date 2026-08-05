<x-app-layout>
    <x-slot name="title">Penyesuaian Stok Opname — SABIRA POSKESTREN</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <h1 class="text-xl font-bold text-[var(--foreground)] tracking-tight">Form Penyesuaian Stok (Stock Opname)</h1>
            <p class="text-xs text-[var(--foreground-muted)] mt-1">Koreksi persediaan stok fisik obat karena rusak, hilang, atau selisih hitung opname.</p>

            @if(session('error'))
                <div class="mt-4 bg-rose-500/10 border-l-4 border-rose-500 p-4 rounded-xl text-xs text-rose-700 dark:text-rose-300 font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('pharmacy.adjustments.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Pilih Batch Obat <span class="text-rose-500">*</span></label>
                    <select name="medicine_batch_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        <option value="">-- Pilih Batch Obat --</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->medicine->generic_name }} (Batch: {{ $b->batch_number }}) — Stok: {{ $b->current_quantity }} {{ $b->medicine->base_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Tipe Penyesuaian <span class="text-rose-500">*</span></label>
                        <select name="movement_type" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            <option value="adjustment_in">Penambahan Stok (+)</option>
                            <option value="adjustment_out">Pengurangan Stok (-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Jumlah Perubahan <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity" min="1" required placeholder="5" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Alasan Penyesuaian (Audit Mandatory) <span class="text-rose-500">*</span></label>
                    <textarea name="reason" required rows="3" placeholder="Jelaskan alasan koreksi stok (contoh: Rusak kemasan / expired / hasil stock opname rutin)..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[var(--border)]">
                    <a href="{{ route('pharmacy.inventory.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-600 text-white hover:bg-amber-700">
                        Simpan Penyesuaian Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
