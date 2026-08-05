<x-app-layout>
    <x-slot name="title">Penerimaan Stok Obat Baru — SABIRA POSKESTREN</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <h1 class="text-xl font-bold text-[var(--foreground)] tracking-tight">Form Penerimaan Stok Obat Baru</h1>
            <p class="text-xs text-[var(--foreground-muted)] mt-1">Pencatatan barang masuk obat-obatan dari Dinas Kesehatan, Puskesmas, atau Pembelian Supplier.</p>

            @if(session('error'))
                <div class="mt-4 bg-rose-500/10 border-l-4 border-rose-500 p-4 rounded-xl text-xs text-rose-700 dark:text-rose-300 font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('pharmacy.receipt.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Pilih Obat <span class="text-rose-500">*</span></label>
                    <select name="medicine_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        <option value="">-- Pilih Obat dari Master Data --</option>
                        @foreach($medicines as $m)
                            <option value="{{ $m->id }}">{{ $m->generic_name }} ({{ $m->code }}) — {{ $m->dosage_form }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Lokasi Penyimpanan <span class="text-rose-500">*</span></label>
                    <select name="stock_location_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Nomor Batch Pabrik <span class="text-rose-500">*</span></label>
                        <input type="text" name="batch_number" required placeholder="Contoh: BATCH-2026-A01" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Tanggal Kedaluwarsa (Expired)</label>
                        <input type="date" name="expiry_date" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Jumlah Diterima <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity" min="1" required placeholder="100" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Nama Supplier / Dinas</label>
                        <input type="text" name="supplier_name" placeholder="Dinkes / Puskesmas Pembina" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Alasan / Catatan Penerimaan</label>
                    <input type="text" name="reason" placeholder="Contoh: Penerimaan bantuan obat rutin semester 1" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[var(--border)]">
                    <a href="{{ route('pharmacy.inventory.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700">
                        Catat Penerimaan Stok Obat
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
