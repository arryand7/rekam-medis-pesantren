<x-app-layout>
    <x-slot name="title">Detail Kunjungan {{ $visit->visit_number }} — SABIRA POSKESTREN</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Kunjungan {{ $visit->visit_number }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300">
                        {{ str_replace('_', ' ', $visit->status) }}
                    </span>
                </div>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Registrasi Kedatangan: {{ $visit->arrived_at->format('d F Y, H:i:s') }} WIB</p>
            </div>

            @if($visit->status !== 'cancelled')
                <div x-data="{ openCancel: false }">
                    <button @click="openCancel = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 transition-colors border border-rose-500/20">
                        Batalkan Kunjungan
                    </button>

                    <!-- Modal Cancellation -->
                    <div x-show="openCancel" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" x-cloak>
                        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] max-w-md w-full space-y-4 shadow-xl">
                            <h3 class="text-lg font-bold text-[var(--foreground)]">Konfirmasi Pembatalan Kunjungan</h3>
                            <p class="text-xs text-[var(--foreground-muted)]">Pembatalan kunjungan bersifat permanen (non-destructive audit trail). Alasan pembatalan wajib diisi.</p>

                            <form action="{{ route('visits.cancel', $visit->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="cancellation_reason" class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Alasan Pembatalan <span class="text-rose-500">*</span></label>
                                    <textarea name="cancellation_reason" id="cancellation_reason" required rows="3" placeholder="Contoh: Terjadi kesalahan input pendaftaran santri..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                                </div>
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="openCancel = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700">
                                        Proses Pembatalan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Patient Info -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Identitas Pasien</h2>
                <div>
                    <div class="text-lg font-bold text-[var(--foreground)]">{{ $visit->patient->person->name }}</div>
                    <div class="text-xs text-[var(--foreground-muted)] font-mono">No. Pasien: {{ $visit->patient->patient_number }}</div>
                    <div class="text-xs text-[var(--foreground-muted)] capitalize mt-1">Tipe: {{ $visit->patient->person->user_type }}</div>
                </div>
                <div class="pt-2 border-t border-[var(--border)]">
                    <a href="{{ route('patients.show', $visit->patient_id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline inline-flex items-center gap-1">
                        Lihat Profil Kesehatan Lengkap &rarr;
                    </a>
                </div>
            </div>

            <!-- Intake Summary -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Ringkasan Intake kedatangan</h2>
                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-[var(--foreground-muted)]">Keluhan Utama:</span>
                        <p class="font-medium text-[var(--foreground)] p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] mt-1">
                            {{ $visit->chief_complaint }}
                        </p>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-[var(--foreground-muted)]">Pengantar:</span>
                        <span class="font-semibold text-[var(--foreground)] capitalize">{{ str_replace('_', ' ', $visit->reporting_type) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[var(--foreground-muted)]">Lokasi Asal:</span>
                        <span class="font-semibold text-[var(--foreground)]">{{ $visit->origin_location ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[var(--foreground-muted)]">Petugas Penerima:</span>
                        <span class="font-semibold text-[var(--foreground)]">{{ $visit->receivingOfficer->name ?? 'System' }}</span>
                    </div>
                </div>
            </div>

        </div>

        @if($visit->cancellation_reason)
            <div class="bg-rose-500/10 border-l-4 border-rose-500 p-4 rounded-2xl text-xs text-rose-700 dark:text-rose-300">
                <strong class="font-bold block">Alasan Pembatalan:</strong>
                <p class="mt-1">{{ $visit->cancellation_reason }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
