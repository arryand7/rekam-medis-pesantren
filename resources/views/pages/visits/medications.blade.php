<x-app-layout>
    <x-slot name="title">Pemberian Obat Santri</x-slot>

    <div class="space-y-6">
        <!-- Patient Context Header Component -->
        <x-patient-context-header :patient="$visit->patient" :visit="$visit" />

        <!-- Visit Stage Navigation Component -->
        <x-visit-stage-nav :visit="$visit" current="medications" />


            <!-- Active Allergy Warning Banner -->
            @if($visit->patient->activeAllergies->count() > 0)
                <div class="p-4 rounded-xl bg-rose-500/10 border-l-4 border-rose-500 space-y-2 text-xs">
                    <div class="font-bold text-rose-700 dark:text-rose-300 flex items-center gap-2">
                        <span>⚠️ PERINGATAN ALERGI AKTIF PASIEN</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($visit->patient->activeAllergies as $allergy)
                            <div class="text-rose-700 dark:text-rose-300 font-medium">
                                • <strong>{{ $allergy->allergen }}</strong> (Reaksi: {{ $allergy->reaction }}, Keparahan: {{ strtoupper($allergy->severity) }})
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-700 dark:text-emerald-300 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-500/10 border-l-4 border-rose-500 p-4 rounded-xl text-xs text-rose-700 dark:text-rose-300 font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <!-- Grid 2 Cols: Order Form & Administration History -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Form Instruksi / Order Obat Baru -->
            <div class="space-y-6">
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Buat Instruksi Obat Baru</h2>

                    <form action="{{ route('visits.medications.orders.store', $visit->id) }}" method="POST" class="space-y-3" x-data="{ hasAllergies: {{ $visit->patient->activeAllergies->count() > 0 ? 'true' : 'false' }} }">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Pilih Obat <span class="text-rose-500">*</span></label>
                            <select name="medicine_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                <option value="">-- Pilih Obat --</option>
                                @foreach($medicines as $m)
                                    <option value="{{ $m->id }}">{{ $m->generic_name }} ({{ $m->strength_text ?? $m->dosage_form }}) — Stok: {{ $m->total_stock }} {{ $m->base_unit }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Dosis <span class="text-rose-500">*</span></label>
                                <input type="text" name="dose_value" required placeholder="500 / 1" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Satuan Dosis <span class="text-rose-500">*</span></label>
                                <input type="text" name="dose_unit" required placeholder="mg / tablet / botol" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Aturan Pakai (Aturan Jaga) <span class="text-rose-500">*</span></label>
                            <input type="text" name="frequency_text" required placeholder="Contoh: 3x1 sehari sesudah makan" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        </div>

                        <template x-if="hasAllergies">
                            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 space-y-2">
                                <label class="block text-xs font-bold text-amber-700 dark:text-amber-300">Konfirmasi Keamanan Alergi Pasien <span class="text-rose-500">*</span></label>
                                <textarea name="allergy_acknowledgement_reason" required rows="2" placeholder="Tuliskan alasan klinis/konfirmasi penelusuran alergi sebelum order obat..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface)] text-xs text-[var(--foreground)]"></textarea>
                            </div>
                        </template>

                        <div class="pt-2 text-right">
                            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                Simpan Instruksi Obat
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Orders & Administration Actions -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Active Medication Orders List -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Daftar Instruksi Obat Aktif Pasien</h2>

                    <div class="space-y-3">
                        @forelse($visit->medicationOrders as $order)
                            <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-3 text-xs" x-data="{ openAdminister: false }">
                                <div class="flex items-center justify-between">
                                    <div class="font-bold text-sm text-[var(--foreground)]">
                                        {{ $order->medicine->generic_name }} ({{ $order->dose_value }} {{ $order->dose_unit }})
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                        {{ $order->status }}
                                    </span>
                                </div>
                                <div class="text-[var(--foreground-muted)] font-medium">
                                    Aturan Pakai: <strong class="text-[var(--foreground)]">{{ $order->frequency_text }}</strong>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-[var(--border)]">
                                    <span class="text-[11px] text-[var(--foreground-muted)] font-mono">Diberikan oleh: {{ $order->orderedBy->name ?? 'System' }}</span>
                                    <button @click="openAdminister = true" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700">
                                        + Catat Pemberian Obat & Potong Stok
                                    </button>
                                </div>

                                <!-- Modal Administer Medication with Batch Selection -->
                                <div x-show="openAdminister" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs text-left" x-cloak>
                                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] max-w-md w-full space-y-4 shadow-xl">
                                        <h3 class="text-lg font-bold text-[var(--foreground)]">Konfirmasi Pemberian Obat ke Pasien</h3>
                                        <p class="text-xs text-[var(--foreground-muted)]">Pilih batch obat aktif yang tersedia di gudang/apotek Poskestren.</p>

                                        <form action="{{ route('visits.medications.administer.store', $order->id) }}" method="POST" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Pilih Batch Obat Aktif <span class="text-rose-500">*</span></label>
                                                <select name="medicine_batch_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] font-mono">
                                                    <option value="">-- Pilih Batch Obat --</option>
                                                    @foreach($order->medicine->batches->where('status', 'active') as $b)
                                                        <option value="{{ $b->id }}">
                                                            Batch {{ $b->batch_number }} (Expired: {{ $b->expiry_date ? $b->expiry_date->format('d M Y') : '-' }}) — Sisa Stok: {{ $b->current_quantity }} {{ $order->medicine->base_unit }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex items-center justify-end gap-2 pt-2">
                                                <button type="button" @click="openAdminister = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                                                    Batal
                                                </button>
                                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white">
                                                    Konfirmasi & Potong Stok
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada instruksi obat dibuat untuk kunjungan medis ini.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Medication Administration Logs & Reversal -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Riwayat Pemberian Obat (Medication Administration Logs)</h2>

                    <div class="space-y-3">
                        @forelse($visit->medicationAdministrations as $admin)
                            <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="font-bold text-[var(--foreground)]">
                                        {{ $admin->medicine->generic_name }} ({{ $admin->dose_value }} {{ $admin->dose_unit }})
                                    </div>
                                    <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold uppercase {{ $admin->status === 'administered' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $admin->status }}
                                    </span>
                                </div>
                                <div class="text-[var(--foreground-muted)]">
                                    Batch: <strong class="font-mono text-[var(--foreground)]">{{ $admin->batch->batch_number ?? '-' }}</strong> • Diserahkan oleh: {{ $admin->administeredBy->name ?? 'System' }} ({{ $admin->administered_at ? $admin->administered_at->format('H:i').' WIB' : '-' }})
                                </div>
                                @if($admin->status === 'administered')
                                    <div class="pt-2" x-data="{ openError: false }">
                                        <button @click="openError = true" class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 underline">
                                            Koreksi (Entered in Error / Reversal Stok)
                                        </button>

                                        <!-- Modal Reversal Entered in Error -->
                                        <div x-show="openError" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs text-left" x-cloak>
                                            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] max-w-md w-full space-y-4 shadow-xl">
                                                <h3 class="text-lg font-bold text-[var(--foreground)]">Pembatalan Catatan Pemberian Obat</h3>
                                                <p class="text-xs text-[var(--foreground-muted)]">Status akan diubah menjadi entered_in_error dan stok batch obat akan dikembalikan secara atomik ke inventaris.</p>

                                                <form action="{{ route('visits.medications.administer.correct', $admin->id) }}" method="POST" class="space-y-3">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Alasan Pembatalan <span class="text-rose-500">*</span></label>
                                                        <textarea name="reason" required rows="2" placeholder="Contoh: Salah pilih pasien / obat batal diberikan..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                                                    </div>

                                                    <div class="flex items-center justify-end gap-2 pt-2">
                                                        <button type="button" @click="openError = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                                                            Batal
                                                        </button>
                                                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white">
                                                            Kembalikan Stok & Batalkan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada riwayat pemberian obat dicatat.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
