<x-app-layout>
    <x-slot name="title">Pengajuan Konsultasi Eksternal — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6 max-w-3xl mx-auto">
        <!-- Patient Banner & Emergency Warning Guard -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-[var(--primary)]/10 text-[var(--primary)] flex items-center justify-center font-bold text-lg border border-[var(--primary)]/20 shrink-0">
                    {{ strtoupper(substr($visit->patient->person->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-[var(--foreground)] tracking-tight">{{ $visit->patient->person->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-[var(--foreground-muted)] mt-1 font-medium">
                        <span>No. Kunjungan: <strong class="font-mono text-[var(--foreground)]">{{ $visit->visit_number }}</strong></span>
                        <span>•</span>
                        <span>Keluhan: <strong class="text-[var(--foreground)]">{{ $visit->chief_complaint }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Emergency Referral Guard Alert -->
            @if($visit->latestAssessment && $visit->latestAssessment->disposition_recommendation === 'emergency_referral_required')
                <div class="p-4 rounded-xl bg-rose-500/10 border-l-4 border-rose-500 space-y-1 text-xs">
                    <div class="font-bold text-rose-700 dark:text-rose-300 flex items-center gap-2">
                        <span>🚨 PERINGATAN DARURAT: RUJUKAN SEGERA DIBUTUHKAN</span>
                    </div>
                    <p class="text-rose-700 dark:text-rose-300 font-medium">
                        Rekomendasi pengkajian klinis medis mewajibkan Rujukan Darurat! Konsultasi jarak jauh <strong>TIDAK BOLEH MENUNDA</strong> pelaksanaan rujukan ke fasilitas kesehatan darurat.
                    </p>
                </div>
            @endif
        </div>

        @if(session('error'))
            <div class="bg-rose-500/10 border-l-4 border-rose-500 p-4 rounded-xl text-xs text-rose-700 dark:text-rose-300 font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form Composer -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <h2 class="text-base font-bold text-[var(--foreground)]">Pengajuan Ringkasan Konsultasi Eksternal</h2>
            <p class="text-xs text-[var(--foreground-muted)]">Pencatatan pertanyaan klinis profesional dan pengiriman ringkasan rekam medis versioned.</p>

            <form action="{{ route('visits.consultations.store', $visit->id) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Pilih Faskes Mitra Penerima Konsultasi <span class="text-rose-500">*</span></label>
                    <select name="healthcare_partner_id" required class="w-full px-3 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        <option value="">-- Pilih Faskes Mitra --</option>
                        @foreach($partners as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ strtoupper($p->partner_type) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Pilih Dokter/Kontak Medis Spesifik (Opsional)</label>
                    <select name="recipient_contact_id" class="w-full px-3 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        <option value="">-- Dokter / Dokter Jaga Faskes --</option>
                        @foreach($partners as $p)
                            @foreach($p->contacts as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->profession }} — {{ $p->name }})</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Tujuan Utama Konsultasi Klinis <span class="text-rose-500">*</span></label>
                    <input type="text" name="purpose" required placeholder="Contoh: Permohonan advis tata laksana observasi demam typhoid" class="w-full px-3 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                </div>

                <div>
                    <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Pertanyaan Klinis Spesifik untuk Dokter Mitra <span class="text-rose-500">*</span></label>
                    <textarea name="clinical_question" required rows="4" placeholder="Tuliskan pertanyaan klinis profesional, kronologi singkat, dan advis tata laksana yang diharapkan..." class="w-full px-3 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Tingkat Urgensi Konsultasi <span class="text-rose-500">*</span></label>
                    <select name="urgency" required class="w-full px-3 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        <option value="routine">Rutin (Perkembangan Kondisi Santri)</option>
                        <option value="urgent">Mendesak (Membutuhkan Advis Dalam Waktu Dekat)</option>
                        <option value="emergency">Darurat (Disertai Persiapan Rujukan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Catatan Pengurangan / Redaksi Informasi (Opsional)</label>
                    <input type="text" name="redaction_notes" placeholder="Catatan minimum necessary privacy jika ada..." class="w-full px-3 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                </div>

                <div class="pt-4 flex items-center justify-between border-t border-[var(--border)]">
                    <a href="{{ route('visits.show', $visit->id) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)]">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                        Finalisasi Ringkasan & Buat Konsultasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
