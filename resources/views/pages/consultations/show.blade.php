<x-app-layout>
    <x-slot name="title">Detail Konsultasi Eksternal — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Top Banner Header -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-700 font-mono">
                            {{ $consultation->urgency }}
                        </span>
                        <h1 class="text-xl font-bold text-[var(--foreground)] tracking-tight">{{ $consultation->purpose }}</h1>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-[var(--foreground-muted)] mt-1 font-medium">
                        <span>Pasien: <strong class="text-[var(--foreground)]">{{ $consultation->medicalVisit->patient->person->name }}</strong></span>
                        <span>•</span>
                        <span>Faskes Mitra: <strong class="text-[var(--foreground)]">{{ $consultation->partner->name }}</strong></span>
                        <span>•</span>
                        <span>Status: <strong class="text-emerald-600 dark:text-emerald-400 font-bold uppercase">{{ $consultation->status }}</strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('consultations.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)]">
                        &larr; Antrean Konsultasi
                    </a>
                </div>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left 2 Cols: Summary Version Payload & Transmissions -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Question & Purpose Details -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Pertanyaan Klinis Profesional</h2>
                    <p class="text-xs text-[var(--foreground)] leading-relaxed bg-[var(--surface-muted)] p-4 rounded-xl border border-[var(--border)] font-mono">
                        {{ $consultation->clinical_question }}
                    </p>
                </div>

                <!-- Versioned Summary Snapshot -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Ringkasan Rekam Medis Versioned (Version 1)</h2>
                        <span class="font-mono text-[11px] text-[var(--foreground-muted)]">Checksum: {{ substr($consultation->latestVersion->checksum ?? '', 0, 16) }}...</span>
                    </div>

                    @if($consultation->latestVersion)
                        <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-3 text-xs font-mono text-[var(--foreground)]">
                            <div><strong>Pasien:</strong> {{ $consultation->latestVersion->summary_payload['patient']['name'] }} (No. RM: {{ $consultation->latestVersion->summary_payload['patient']['patient_number'] }})</div>
                            <div><strong>Keluhan Utama:</strong> {{ $consultation->latestVersion->summary_payload['visit']['chief_complaint'] }}</div>
                            <div><strong>Ringkasan Pengkajian:</strong> {{ $consultation->latestVersion->summary_payload['assessment_summary'] }}</div>
                            <div><strong>Impresi Diagnostik:</strong> {{ $consultation->latestVersion->summary_payload['working_diagnosis'] ?? 'Belum ada' }}</div>
                            <div><strong>Alergi Aktif:</strong> {{ implode(', ', $consultation->latestVersion->summary_payload['active_allergies']) ?: 'Tidak ada alergi aktif' }}</div>
                        </div>
                    @endif
                </div>

                <!-- Transmission Status & Actions -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Riwayat Transmisi & Pengiriman</h2>

                        @if($consultation->status === 'ready')
                            <form action="{{ route('consultations.transmit', $consultation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                    🚀 Kirim Ringkasan ke Mitra Faskes
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="space-y-2">
                        @forelse($consultation->transmissions as $t)
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] flex items-center justify-between text-xs font-mono">
                                <div>
                                    <span class="font-bold text-[var(--foreground)]">Channel: {{ $t->channel }}</span> • Ext Ref: {{ $t->external_reference }}
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700">
                                    {{ $t->status }} ({{ $t->sent_at?->format('H:i').' WIB' }})
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada pengiriman dilakukan.</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Right Col: Response External Advice & Local Decision Forms -->
            <div class="space-y-6">

                <!-- Form Record External Advice -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Catat Respons Advice Eksternal</h2>

                    <form action="{{ route('consultations.advice.store', $consultation->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Nama Dokter / Tenaga Medis Mitra <span class="text-rose-500">*</span></label>
                            <input type="text" name="clinician_name" required value="{{ $consultation->recipientContact->name ?? '' }}" placeholder="dr. H. Ahmad Dahlan, Sp.PD" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Profesi / Keahlian <span class="text-rose-500">*</span></label>
                            <input type="text" name="clinician_profession" required value="{{ $consultation->recipientContact->profession ?? 'Dokter Spesialis' }}" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Isi Nasehat / Advice Klinis <span class="text-rose-500">*</span></label>
                            <textarea name="advice_text" required rows="4" placeholder="Tuliskan nasehat klinis profesional yang diterima dari dokter faskes mitra..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Rekomendasi Langkah Lanjutan (Opsional)</label>
                            <input type="text" name="recommended_next_step" placeholder="Contoh: Lanjutkan observasi 24 jam / Rujuk jika demam > 39 C" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                        </div>

                        <div class="pt-2 text-right">
                            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700">
                                Simpan Advice Eksternal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Form Record Local Clinical Decision -->
                @if($consultation->latestAdvice)
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Penetapan Keputusan Klinis Lokal Poskestren</h2>

                        <form action="{{ route('consultations.decision.store', $consultation->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Keputusan Klinis Lokal <span class="text-rose-500">*</span></label>
                                <select name="decision_type" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                    <option value="continue_observation">Lanjutkan Observasi di Poskestren</option>
                                    <option value="continue_current_care">Lanjutkan Perawatan & Terapi Saat Ini</option>
                                    <option value="rest_recommended">Istirahat di Kamar Santri</option>
                                    <option value="referral_recommended">Rekomendasi Rujukan Non-Darurat</option>
                                    <option value="emergency_referral_required">Wajib Rujukan DaruratSegera</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Pertimbangan / Rationale <span class="text-rose-500">*</span></label>
                                <textarea name="rationale" required rows="3" placeholder="Tuliskan alasan pertimbangan tim medis Poskestren setelah menerima advice..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                            </div>

                            <div class="pt-2 text-right">
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white hover:bg-blue-700">
                                    Finalisasi Keputusan & Selesaikan
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
