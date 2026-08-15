<x-app-layout>
    <x-slot name="title">Detail Konsultasi Eksternal</x-slot>

    <div class="space-y-6">
        <!-- Patient Context Header -->
        <x-patient-context-header :patient="$consultation->medicalVisit->patient" :visit="$consultation->medicalVisit" />

        <!-- Visit Stage Stepper Navigation -->
        <x-visit-stage-nav :visit="$consultation->medicalVisit" current="consultations" />

        <!-- Clinical Consultation Advisory Notice -->
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-3">
            <span class="text-base shrink-0">ℹ️</span>
            <div>
                <strong>Prinsip Klinis Konsultasi Jarak Jauh:</strong> Respon dan saran dari fasilitas mitra luar adalah <em>External Clinical Advice</em> (rekomendasi). Keputusan medis final, peresepan obat, dan tindakan operasional tetap merupakan wewenang mandiri dokter/petugas kesehatan POSKESTREN.
            </div>
        </div>

        <!-- Top Banner Header -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 font-mono">
                            {{ $consultation->urgency }}
                        </span>
                        <h1 class="text-lg font-bold text-[var(--foreground)] tracking-tight">{{ $consultation->purpose }}</h1>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-[var(--foreground-muted)] mt-1 font-medium">
                        <span>Faskes Mitra: <strong class="text-[var(--foreground)]">{{ $consultation->partner->name }}</strong></span>
                        <span>•</span>
                        <span>Transport: <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">LOCAL-DEVELOPMENT / SIMULATED TRANSPORT</span></span>
                        <span>•</span>
                        <span>Status: <strong class="text-emerald-600 dark:text-emerald-400 font-bold uppercase">{{ $consultation->status }}</strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('visits.show', $consultation->medical_visit_id) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--surface)] transition-colors">
                        &larr; Workspace Kunjungan
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

                <!-- Display Received External Advices (If any) -->
                @if($consultation->externalAdvices->count() > 0)
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                                <span>🩺</span> Saran / Advice Klinis Eksternal
                            </h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                {{ $consultation->externalAdvices->count() }} Respon
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach($consultation->externalAdvices as $advice)
                                <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-[var(--foreground)]">{{ $advice->clinician_name }}</span>
                                        <span class="text-[11px] text-[var(--foreground-muted)]">{{ $advice->clinician_profession ?? 'Dokter Spesialis' }}</span>
                                    </div>
                                    <p class="text-xs text-[var(--foreground)] p-3 rounded-lg bg-[var(--surface)] border border-[var(--border)] leading-relaxed">
                                        {{ $advice->advice_text }}
                                    </p>
                                    @if($advice->recommended_next_step)
                                        <div class="text-[11px] text-[var(--foreground-muted)]">
                                            <strong>Anjuran Lanjutan:</strong> {{ $advice->recommended_next_step }}
                                        </div>
                                    @endif
                                    <div class="text-[10px] text-[var(--foreground-muted)] pt-1 flex items-center justify-between">
                                        <span>Diterima: {{ $advice->received_at?->format('d M Y, H:i') ?? '-' }} WIB</span>
                                        <span>Petugas: {{ $advice->recordedBy->name ?? 'POSKESTREN' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Display Finalized Local Decision (If any) -->
                @if($consultation->latestDecision)
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-blue-200 dark:border-blue-800/60 shadow-xs space-y-3">
                        <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300 flex items-center gap-2">
                                <span>⚖️</span> Keputusan Klinis Lokal POSKESTREN
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300">
                                Final
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] text-xs space-y-2">
                            <div class="font-bold text-[var(--foreground)]">
                                Tipe: {{ ucfirst(str_replace('_', ' ', $consultation->latestDecision->decision_type)) }}
                            </div>
                            <p class="text-[var(--foreground-muted)] leading-relaxed">
                                <strong>Rationale:</strong> {{ $consultation->latestDecision->rationale }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Form Record External Advice (Visible if not completed) -->
                @if($consultation->status !== 'completed' && $consultation->status !== 'cancelled')
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Catat Respons Advice Eksternal</h2>

                        <form action="{{ route('consultations.advice.store', $consultation->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Nama Dokter / Tenaga Medis Mitra <span class="text-rose-500">*</span></label>
                                <input type="text" name="clinician_name" required value="{{ $consultation->recipientContact->name ?? '' }}" placeholder="dr. Contoh Mitra, Sp.PD" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
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
                    @if($consultation->latestAdvice && !$consultation->latestDecision)
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
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
