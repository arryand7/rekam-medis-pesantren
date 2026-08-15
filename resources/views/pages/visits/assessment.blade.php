<x-app-layout>
    <x-slot name="title">Workspace Pengkajian Klinis</x-slot>

    <div class="space-y-6">
        <!-- Patient Context Header Component -->
        <x-patient-context-header :patient="$visit->patient" :visit="$visit" />

        <!-- Visit Stage Navigation Component -->
        <x-visit-stage-nav :visit="$visit" current="assessment" />


        <!-- Alert Active Allergy Warning -->
        @if($visit->patient->activeAllergies->count() > 0)
            <div class="bg-amber-500/10 border-l-4 border-amber-500 p-4 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h3 class="text-xs font-bold text-amber-800 dark:text-amber-200 uppercase tracking-wider">Peringatan Alergi Aktif Pasien</h3>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @foreach($visit->patient->activeAllergies as $allergy)
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-100">
                                ⚠️ {{ $allergy->allergen }} ({{ $allergy->reaction ?? 'Tidak ada deskripsi' }})
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-700 dark:text-emerald-300 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Main Grid: Vital Signs & Assessment Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Col 1: Vital Signs Form & History -->
            <div class="space-y-6">
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Pemeriksaan Tanda Vital</h2>

                    <form action="{{ route('visits.vital-signs.store', $visit->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-medium text-[var(--foreground-muted)] mb-1">Suhu (°C)</label>
                                <input type="number" step="0.1" name="temperature_c" placeholder="36.5" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block font-medium text-[var(--foreground-muted)] mb-1">SpO2 (%)</label>
                                <input type="number" name="spo2_percent" placeholder="98" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block font-medium text-[var(--foreground-muted)] mb-1">TD Sistolik (mmHg)</label>
                                <input type="number" name="systolic_bp" placeholder="120" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block font-medium text-[var(--foreground-muted)] mb-1">TD Diastolik (mmHg)</label>
                                <input type="number" name="diastolic_bp" placeholder="80" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block font-medium text-[var(--foreground-muted)] mb-1">Nadi (bpm)</label>
                                <input type="number" name="pulse_bpm" placeholder="80" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block font-medium text-[var(--foreground-muted)] mb-1">Napas (x/mnt)</label>
                                <input type="number" name="respiratory_rate" placeholder="18" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)]">
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-between">
                            <label class="inline-flex items-center gap-1.5 text-xs text-[var(--foreground-muted)]">
                                <input type="checkbox" name="finalize" value="1" checked class="rounded border-[var(--border)] text-[var(--primary)]">
                                Finalisasi Langsung
                            </label>
                            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                Simpan Vital Signs
                            </button>
                        </div>
                    </form>

                    <!-- History Vital Signs -->
                    <div class="pt-4 border-t border-[var(--border)] space-y-2">
                        <h3 class="text-xs font-semibold text-[var(--foreground-muted)]">Riwayat Tanda Vital Kunjungan Ini:</h3>
                        @forelse($visit->vitalSigns as $vs)
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[var(--foreground)]">{{ $vs->recorded_at->format('H:i') }} WIB</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                        {{ $vs->status }}
                                    </span>
                                </div>
                                <div class="text-[var(--foreground-muted)]">
                                    Suhu: {{ $vs->temperature_c ? $vs->temperature_c.'°C' : '-' }} |
                                    TD: {{ $vs->systolic_bp && $vs->diastolic_bp ? $vs->systolic_bp.'/'.$vs->diastolic_bp : '-' }} |
                                    SpO2: {{ $vs->spo2_percent ? $vs->spo2_percent.'%' : '-' }}
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada tanda vital tercatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Col 2 & 3: Clinical Assessment & Initial Actions Form -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Assessment Form -->
                <form action="{{ route('visits.assessment.store', $visit->id) }}" method="POST" class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-6">
                    @csrf
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Pengkajian Klinis Medis (Assessment)</h2>
                        <span class="text-xs text-[var(--foreground-muted)]">Author: {{ Auth::user()->name }}</span>
                    </div>

                    @php
                        $assessment = $visit->latestAssessment;
                    @endphp

                    <!-- Anamnesis -->
                    <div class="space-y-1.5">
                        <label for="history_current_illness" class="block text-xs font-bold text-[var(--foreground-muted)] uppercase">Anamnesis / Keluhan & Riwayat Penyakit Sekarang <span class="text-rose-500">*</span></label>
                        <textarea name="history_current_illness" id="history_current_illness" rows="3" required placeholder="Catat hasil wawancara keluhan, durasi, sifat nyeri, dan gejala tambahan..." class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">{{ old('history_current_illness', $assessment->history_current_illness ?? '') }}</textarea>
                    </div>

                    <!-- Physical Examination -->
                    <div class="space-y-1.5">
                        <label for="examination_findings" class="block text-xs font-bold text-[var(--foreground-muted)] uppercase">Hasil Pemeriksaan Fisik <span class="text-rose-500">*</span></label>
                        <textarea name="examination_findings" id="examination_findings" rows="3" required placeholder="Catat hasil inspeksi, auskultasi, palpasi (kepala, mata, tenggorokan, paru, abdomen, ekstremitas)..." class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">{{ old('examination_findings', $assessment->examination_findings ?? '') }}</textarea>
                    </div>

                    <!-- Assessment Summary & Working Impression -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="assessment_summary" class="block text-xs font-bold text-[var(--foreground-muted)] uppercase">Ringkasan Pengkajian / Impresi <span class="text-rose-500">*</span></label>
                            <textarea name="assessment_summary" id="assessment_summary" rows="2" required placeholder="Ringkasan kesimpulan kondisi klinis santri..." class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">{{ old('assessment_summary', $assessment->assessment_summary ?? '') }}</textarea>
                        </div>

                        <div class="space-y-1.5">
                            <label for="working_diagnosis" class="block text-xs font-bold text-[var(--foreground-muted)] uppercase">Diagnosis Kerja / Dugaan Diagnostik</label>
                            <textarea name="working_diagnosis" id="working_diagnosis" rows="2" placeholder="Contoh: Febris H-1 e.c. ISPA Suspek Vulnus Laceratum..." class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">{{ old('working_diagnosis', $assessment->working_diagnosis ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Disposition Recommendation -->
                    <div class="space-y-1.5">
                        <label for="disposition_recommendation" class="block text-xs font-bold text-[var(--foreground-muted)] uppercase">Rekomendasi Disposisi <span class="text-rose-500">*</span></label>
                        <select name="disposition_recommendation" id="disposition_recommendation" required class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">
                            <option value="">-- Pilih Rekomendasi Disposisi --</option>
                            <option value="return_to_activity" {{ (old('disposition_recommendation', $assessment->disposition_recommendation ?? '') === 'return_to_activity') ? 'selected' : '' }}>Kembali Beraktivitas / Belajar di Kelas</option>
                            <option value="rest_at_poskestren" {{ (old('disposition_recommendation', $assessment->disposition_recommendation ?? '') === 'rest_at_poskestren') ? 'selected' : '' }}>Istirahat / Observasi di Ruang Poskestren</option>
                            <option value="observation_required" {{ (old('disposition_recommendation', $assessment->disposition_recommendation ?? '') === 'observation_required') ? 'selected' : '' }}>Memerlukan Observasi Lanjutan Poskestren</option>
                            <option value="external_consultation_required" {{ (old('disposition_recommendation', $assessment->disposition_recommendation ?? '') === 'external_consultation_required') ? 'selected' : '' }}>Memerlukan Konsultasi Puskesmas / Faskes Luar</option>
                            <option value="referral_required" {{ (old('disposition_recommendation', $assessment->disposition_recommendation ?? '') === 'referral_required') ? 'selected' : '' }}>Rujukan Faskes Lanjutan / Rumah Sakit</option>
                            <option value="emergency_referral_required" {{ (old('disposition_recommendation', $assessment->disposition_recommendation ?? '') === 'emergency_referral_required') ? 'selected' : '' }}>⚠️ RUJUKAN DARURAT (EMERGENCY REFERRAL)</option>
                        </select>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-[var(--border)]">
                        <button type="submit" name="finalize" value="0" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--border)]">
                            Simpan Draft
                        </button>
                        <button type="submit" name="finalize" value="1" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors shadow-xs">
                            Finalisasi & Selesaikan Assessment
                        </button>
                    </div>
                </form>

                <!-- Initial Actions Non-Medication Form -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Tindakan Awal Non-Obat (Initial Actions)</h2>

                    <form action="{{ route('visits.actions.store', $visit->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="block text-xs font-medium text-[var(--foreground-muted)]">Kategori Tindakan</label>
                                <select name="action_type" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                    <option value="first_aid">P3K / Pertolongan Pertama</option>
                                    <option value="wound_care">Perawatan Luka / Kompres</option>
                                    <option value="hydration">Hidrasi / Dukungan Minum Air</option>
                                    <option value="rest_recommendation">Rekomendasi Tirah Baring</option>
                                    <option value="monitoring">Pemantauan Tanda Vital Berkala</option>
                                    <option value="other">Tindakan Non-Obat Lainnya</option>
                                </select>
                            </div>
                            <div class="md:col-span-2 space-y-1">
                                <label class="block text-xs font-medium text-[var(--foreground-muted)]">Deskripsi Tindakan Non-Obat</label>
                                <input type="text" name="description" required placeholder="Contoh: Pembersihan luka lecet lutut dengan NaCl 0.9% dan povidone iodine..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900">
                                Catat Tindakan Non-Obat
                            </button>
                        </div>
                    </form>

                    <!-- Table of Actions Performed -->
                    <div class="pt-3 border-t border-[var(--border)] space-y-2">
                        <h3 class="text-xs font-semibold text-[var(--foreground-muted)]">Daftar Tindakan Non-Obat Tercatat:</h3>
                        @forelse($visit->actions as $act)
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-[var(--foreground)] uppercase text-[11px] px-2 py-0.5 rounded bg-[var(--surface)] border border-[var(--border)]">
                                        {{ str_replace('_', ' ', $act->action_type) }}
                                    </span>
                                    <p class="font-medium text-[var(--foreground)] mt-1">{{ $act->description }}</p>
                                </div>
                                <div class="text-right text-[11px] text-[var(--foreground-muted)]">
                                    <div>{{ $act->performed_at->format('H:i:s') }} WIB</div>
                                    <div>{{ $act->performedBy->name ?? 'System' }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada tindakan non-obat tercatat.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
