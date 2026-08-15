<x-app-layout>
    <x-slot name="title">Workspace Kunjungan {{ $visit->visit_number }}</x-slot>

    <div class="space-y-6">
        <!-- Patient Context Header Component -->
        <x-patient-context-header :patient="$visit->patient" :visit="$visit" />

        <!-- Visit Stage Navigation Component -->
        <x-visit-stage-nav :visit="$visit" current="overview" />

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Next Action Suggestion Banner (UI Guidance Engine) -->
        @php
            $hasVitals = $visit->latestVitalSign !== null || $visit->vitalSigns->count() > 0;
            $hasAssessment = $visit->latestAssessment !== null;
            $hasDischarge = $visit->discharge !== null;
            $isCancelled = $visit->status === 'cancelled';

            $activeObs = $visit->activeObservationEpisode ?? $visit->observationEpisodes->firstWhere('status', 'active');
            $activeRef = $visit->activeReferral ?? $visit->referrals->first();
            $latestConsult = $visit->consultations->first();

            // Next Action Logic
            $nextActionText = '';
            $nextActionRoute = '';
            $nextActionBtn = '';
            $nextActionColor = 'bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]';

            if ($activeRef && $activeRef->status === 'return_recorded' && (! $activeRef->referralReturn?->review)) {
                $nextActionText = 'Santri telah kembali dari faskes rujukan. Silakan lakukan telaah medis / review kepulangan rujukan.';
                $nextActionRoute = route('referrals.show', $activeRef->id);
                $nextActionBtn = 'Review Kepulangan Rujukan &rarr;';
                $nextActionColor = 'bg-amber-600 text-white hover:bg-amber-700';
            } elseif ($latestConsult && $latestConsult->latestAdvice && (! $latestConsult->latestDecision)) {
                $nextActionText = 'Saran klinis dari faskes mitra telah diterima. Silakan catat keputusan medis lokal POSKESTREN.';
                $nextActionRoute = route('consultations.show', $latestConsult->id);
                $nextActionBtn = 'Catat Keputusan Lokal &rarr;';
                $nextActionColor = 'bg-purple-600 text-white hover:bg-purple-700';
            } elseif ($activeObs && $activeObs->isActive()) {
                if ($activeObs->isOverdue()) {
                    $nextActionText = 'Jadwal pemantauan berkala telah tercapai! Segera lakukan dan catat monitoring observasi santri.';
                    $nextActionColor = 'bg-rose-600 text-white hover:bg-rose-700';
                } else {
                    $nextActionText = 'Santri sedang dalam observasi di '.$activeObs->location_label.' ('.$activeObs->bed_label.'). Lanjutkan monitoring berkala.';
                }
                $nextActionRoute = route('observations.show', $activeObs->id);
                $nextActionBtn = 'Buka Lembar Observasi &rarr;';
            } elseif (! $hasVitals) {
                $nextActionText = 'Tanda vital belum dicatat. Silakan lakukan pemeriksaan fisik awal dan rekam tanda vital pasien.';
                $nextActionRoute = route('visits.assessment', $visit->id);
                $nextActionBtn = 'Catat Tanda Vital &rarr;';
            } elseif (! $hasAssessment) {
                $nextActionText = 'Tanda vital telah tersedia. Lanjutkan ke pengisian anamnesis dan impresi diagnostik pada formulir SOAP.';
                $nextActionRoute = route('visits.assessment', $visit->id);
                $nextActionBtn = 'Lanjutkan ke SOAP &rarr;';
            } elseif (! $hasDischarge) {
                $nextActionText = 'Pengkajian klinis telah selesai. Anda dapat meresepkan obat, mengarahkan observasi, merujuk ke RS, atau memulangkan santri.';
                $nextActionRoute = route('visits.discharge', $visit->id);
                $nextActionBtn = 'Proses Kepulangan &rarr;';
                $nextActionColor = 'bg-emerald-600 text-white hover:bg-emerald-500';
            } else {
                $nextActionText = 'Kunjungan telah selesai. Seluruh catatan medis, rencana kontrol, dan handoff operasional telah diterbitkan.';
                $nextActionRoute = route('visits.discharge', $visit->id);
                $nextActionBtn = 'Lihat Resume Medis &rarr;';
                $nextActionColor = 'bg-slate-700 text-white hover:bg-slate-600';
            }
        @endphp

        @if(!$isCancelled)
            <div class="bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-950/40 dark:to-indigo-950/40 border border-sky-200/80 dark:border-sky-800/60 p-4 sm:p-5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                        💡
                    </div>
                    <div>
                        <h3 class="text-xs uppercase font-bold text-sky-900 dark:text-sky-200 tracking-wider">Panduan Langkah Pelayanan Selanjutnya</h3>
                        <p class="text-xs text-sky-800 dark:text-sky-300 mt-0.5 font-medium">
                            {{ $nextActionText }}
                        </p>
                    </div>
                </div>

                @if($nextActionRoute && $nextActionBtn)
                    <div class="shrink-0 flex items-center gap-2">
                        <a href="{{ $nextActionRoute }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-colors shadow-xs {{ $nextActionColor }}">
                            <span>{!! $nextActionBtn !!}</span>
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <!-- Main Workspace Overview Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Col 1: Intake & Arrival Information -->
            <div class="space-y-6">
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Informasi Kedatangan</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300">
                            {{ str_replace('_', ' ', $visit->status) }}
                        </span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-[var(--foreground-muted)] block mb-1">Keluhan Utama:</span>
                            <p class="font-medium text-[var(--foreground)] p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] leading-relaxed">
                                {{ $visit->chief_complaint }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <span class="text-[var(--foreground-muted)]">Waktu Kedatangan:</span>
                            <span class="font-bold text-[var(--foreground)]">{{ $visit->arrived_at->format('d M Y, H:i') }} WIB</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-[var(--foreground-muted)]">Tipe Pengantar:</span>
                            <span class="font-semibold text-[var(--foreground)] capitalize">{{ str_replace('_', ' ', $visit->reporting_type ?? 'Datang Sendiri') }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-[var(--foreground-muted)]">Lokasi Asal / Asrama:</span>
                            <span class="font-semibold text-[var(--foreground)]">{{ $visit->origin_location ?? '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-[var(--foreground-muted)]">Petugas Penerima:</span>
                            <span class="font-semibold text-[var(--foreground)]">{{ $visit->receivingOfficer->name ?? 'Petugas Intake' }}</span>
                        </div>
                    </div>

                    @if($visit->status !== 'cancelled')
                        <div class="pt-4 border-t border-[var(--border)]" x-data="{ openCancel: false }">
                            <button @click="openCancel = true"
                                    type="button"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 hover:bg-rose-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Batalkan Kunjungan Ini</span>
                            </button>

                            <!-- Modal Cancellation -->
                            <div x-show="openCancel" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" x-cloak>
                                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] max-w-md w-full space-y-4 shadow-2xl">
                                    <h3 class="text-lg font-bold text-[var(--foreground)]">Konfirmasi Pembatalan Kunjungan</h3>
                                    <p class="text-xs text-[var(--foreground-muted)]">Pembatalan kunjungan medis tercatat secara permanen di audit log. Alasan pembatalan wajib diisi secara jelas.</p>

                                    <form action="{{ route('visits.cancel', $visit->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label for="cancellation_reason" class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">
                                                Alasan Pembatalan <span class="text-rose-500">*</span>
                                            </label>
                                            <textarea name="cancellation_reason" id="cancellation_reason" required rows="3" placeholder="Contoh: Terjadi duplikasi input pendaftaran santri..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)] focus:ring-2 focus:ring-rose-500"></textarea>
                                        </div>
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" @click="openCancel = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                                                Tutup
                                            </button>
                                            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700">
                                                Ya, Batalkan Kunjungan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-xs text-rose-700 dark:text-rose-300">
                            <strong>Kunjungan Dibatalkan:</strong>
                            <p class="mt-1">{{ $visit->cancellation_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Col 2 & 3: Clinical Workspace Cards -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. Tanda Vital Card -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground)]">1. Pemeriksaan Tanda Vital</h2>
                            @if($hasVitals)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                    Tercatat
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('visits.assessment', $visit->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                            {{ $hasVitals ? 'Ubah / Perbarui Vital' : '+ Catat Tanda Vital' }} &rarr;
                        </a>
                    </div>

                    @php
                        $latestVital = $visit->latestVitalSign ?? $visit->vitalSigns->first();
                    @endphp

                    @if($latestVital)
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                                <div class="text-[10px] uppercase font-bold text-[var(--foreground-muted)]">Tekanan Darah</div>
                                <div class="text-lg font-bold text-[var(--foreground)] mt-1">
                                    {{ $latestVital->systolic_bp ?? '-' }}/{{ $latestVital->diastolic_bp ?? '-' }}
                                    <span class="text-[10px] font-normal text-[var(--foreground-muted)]">mmHg</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                                <div class="text-[10px] uppercase font-bold text-[var(--foreground-muted)]">Suhu Badan</div>
                                <div class="text-lg font-bold text-[var(--foreground)] mt-1">
                                    {{ $latestVital->temperature_c ?? '-' }}
                                    <span class="text-[10px] font-normal text-[var(--foreground-muted)]">°C</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                                <div class="text-[10px] uppercase font-bold text-[var(--foreground-muted)]">Denyut Nadi</div>
                                <div class="text-lg font-bold text-[var(--foreground)] mt-1">
                                    {{ $latestVital->pulse_bpm ?? '-' }}
                                    <span class="text-[10px] font-normal text-[var(--foreground-muted)]">bpm</span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                                <div class="text-[10px] uppercase font-bold text-[var(--foreground-muted)]">Saturasi O2</div>
                                <div class="text-lg font-bold text-[var(--foreground)] mt-1">
                                    {{ $latestVital->spo2_percent ?? '-' }}
                                    <span class="text-[10px] font-normal text-[var(--foreground-muted)]">%</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-6 text-center rounded-xl bg-[var(--surface-muted)] border border-dashed border-[var(--border)] space-y-2">
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada catatan tanda vital untuk kunjungan ini.</p>
                            <a href="{{ route('visits.assessment', $visit->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                Catat Tanda Vital Pasien
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 2. Pengkajian Klinis SOAP Card -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground)]">2. Pengkajian Medis & SOAP</h2>
                            @if($visit->latestAssessment)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $visit->latestAssessment->status === 'finalized' ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300' }}">
                                    {{ $visit->latestAssessment->status }}
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('visits.assessment', $visit->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                            {{ $visit->latestAssessment ? 'Buka Pengkajian Lengkap' : '+ Mulai Pengkajian' }} &rarr;
                        </a>
                    </div>

                    @if($visit->latestAssessment)
                        <div class="space-y-3 text-xs">
                            <div class="p-3.5 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                                <span class="text-[10px] font-bold uppercase text-[var(--foreground-muted)] block mb-1">Diagnosis Kerja / Impresi Medis:</span>
                                <div class="text-sm font-bold text-[var(--foreground)]">{{ $visit->latestAssessment->working_diagnosis ?? 'Belum terisi' }}</div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                                    <span class="text-[10px] font-bold uppercase text-[var(--foreground-muted)] block mb-1">Subjektif (Anamnesis):</span>
                                    <p class="text-xs text-[var(--foreground)] line-clamp-3">{{ $visit->latestAssessment->subjective ?? '-' }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                                    <span class="text-[10px] font-bold uppercase text-[var(--foreground-muted)] block mb-1">Objektif (Temuan Fisik):</span>
                                    <p class="text-xs text-[var(--foreground)] line-clamp-3">{{ $visit->latestAssessment->objective ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-6 text-center rounded-xl bg-[var(--surface-muted)] border border-dashed border-[var(--border)] space-y-2">
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada pengkajian klinis SOAP yang didokumentasikan.</p>
                            <a href="{{ route('visits.assessment', $visit->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                Input Pengkajian SOAP
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 3. Ruang Observasi Rawat Inap Card -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground)]">3. Ruang Observasi Rawat Inap</h2>
                            @if($activeObs)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $activeObs->isActive() ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    {{ $activeObs->status }}
                                </span>
                            @endif
                        </div>
                        @if($activeObs)
                            <a href="{{ route('observations.show', $activeObs->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                                Buka Lembar Observasi &rarr;
                            </a>
                        @else
                            <a href="{{ route('observations.index') }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                                Lihat Ruang Observasi &rarr;
                            </a>
                        @endif
                    </div>

                    @if($activeObs)
                        <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2 text-xs">
                            <div class="flex items-center justify-between font-semibold">
                                <span class="text-[var(--foreground)]">Lokasi: {{ $activeObs->location_label }} ({{ $activeObs->bed_label ?? '-' }})</span>
                                <span class="text-[var(--foreground-muted)]">PJ: {{ $activeObs->responsibleOfficer->name ?? 'Petugas Jaga' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-[var(--foreground-muted)]">
                                <span>Total Monitoring: <strong class="text-[var(--foreground)]">{{ $activeObs->records->count() }} lembar</strong></span>
                                <span>Mulai: {{ $activeObs->created_at?->format('d M Y, H:i') }} WIB</span>
                            </div>
                        </div>
                    @else
                        <div class="p-6 text-center rounded-xl bg-[var(--surface-muted)] border border-dashed border-[var(--border)] space-y-2">
                            <p class="text-xs text-[var(--foreground-muted)]">Santri tidak sedang dalam masa episode observasi rawat inap.</p>
                            <a href="{{ route('observations.index') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[var(--surface)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--surface-muted)]">
                                Direktori Observasi Poskestren
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 4. Tele-Konsultasi Eksternal Card -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground)]">4. Tele-Konsultasi Eksternal</h2>
                            @if($latestConsult)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300">
                                    {{ $latestConsult->status }}
                                </span>
                            @endif
                        </div>
                        @if($latestConsult)
                            <a href="{{ route('consultations.show', $latestConsult->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                                Buka Konsultasi &rarr;
                            </a>
                        @else
                            <a href="{{ route('visits.consultations.create', $visit->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                                + Ajukan Konsultasi &rarr;
                            </a>
                        @endif
                    </div>

                    @if($latestConsult)
                        <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[var(--foreground)]">{{ $latestConsult->purpose }}</span>
                                <span class="text-xs text-[var(--foreground-muted)] font-medium">{{ $latestConsult->partner->name ?? 'Faskes Mitra' }}</span>
                            </div>
                            <div class="text-[11px] text-[var(--foreground-muted)]">
                                Advis Eksternal:
                                @if($latestConsult->latestAdvice)
                                    <strong class="text-emerald-600 dark:text-emerald-400">✓ Diterima</strong>
                                    @if(!$latestConsult->latestDecision)
                                        <span class="text-amber-600 font-semibold">(Menunggu keputusan lokal)</span>
                                    @endif
                                @else
                                    <span class="text-slate-500">Belum ada respon</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-6 text-center rounded-xl bg-[var(--surface-muted)] border border-dashed border-[var(--border)] space-y-2">
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada permohonan konsultasi klinis eksternal untuk kunjungan ini.</p>
                            <a href="{{ route('visits.consultations.create', $visit->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                + Ajukan Konsultasi ke Dokter Mitra
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 5. Rujukan Rumah Sakit / Faskes Card -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground)]">5. Rujukan RS / Faskes Lanjutan</h2>
                            @if($activeRef)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300">
                                    {{ str_replace('_', ' ', $activeRef->status) }}
                                </span>
                            @endif
                        </div>
                        @if($activeRef)
                            <a href="{{ route('referrals.show', $activeRef->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                                Buka Detail Rujukan &rarr;
                            </a>
                        @else
                            <a href="{{ route('visits.referrals.create', $visit->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                                + Buat Rujukan RS &rarr;
                            </a>
                        @endif
                    </div>

                    @if($activeRef)
                        <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-[var(--foreground)]">{{ $activeRef->referral_number }}</span>
                                <span class="font-semibold text-[var(--foreground)]">{{ $activeRef->partner->name ?? 'RS / Faskes Rujukan' }}</span>
                            </div>
                            <div class="text-[11px] text-[var(--foreground-muted)]">
                                Urgensi: <strong class="uppercase text-[var(--foreground)]">{{ $activeRef->urgency }}</strong> •
                                Transport: <strong>{{ $activeRef->transports->count() > 0 ? 'Tersedia' : 'Belum diatur' }}</strong> •
                                Serah Terima: <strong>{{ $activeRef->handovers->count() > 0 ? 'Selesai' : 'Belum' }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="p-6 text-center rounded-xl bg-[var(--surface-muted)] border border-dashed border-[var(--border)] space-y-2">
                            <p class="text-xs text-[var(--foreground-muted)]">Kunjungan ini belum memiliki rujukan ke rumah sakit / puskesmas.</p>
                            <a href="{{ route('visits.referrals.create', $visit->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                + Terbitkan Surat Rujukan
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 6. Resep & Obat Card -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground)]">6. Resep & Dispensing Obat</h2>
                        <a href="{{ route('visits.medications.index', $visit->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                            Kelola Resep Obat &rarr;
                        </a>
                    </div>

                    @if($visit->medicationOrders->count() > 0)
                        <div class="space-y-2">
                            @foreach($visit->medicationOrders as $order)
                                <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                    <div>
                                        <span class="font-bold text-[var(--foreground)]">{{ $order->medicine->name ?? 'Obat' }}</span>
                                        <div class="text-[11px] text-[var(--foreground-muted)]">
                                            Dosis: {{ $order->dosage_instruction }} • Jumlah: {{ $order->quantity_prescribed }} {{ $order->medicine->unit ?? 'tablet' }}
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase {{ $order->status === 'administered' ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300' }}">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center rounded-xl bg-[var(--surface-muted)] border border-dashed border-[var(--border)] space-y-2">
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada obat yang diresepkan untuk kunjungan ini.</p>
                            <a href="{{ route('visits.medications.index', $visit->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                + Resepkan Obat
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 7. Disposisi & Kepulangan Card -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[var(--border)]">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground)]">7. Disposisi & Kepulangan Medis</h2>
                        <a href="{{ route('visits.discharge', $visit->id) }}" class="text-xs font-bold text-[var(--primary)] hover:underline">
                            {{ $visit->discharge ? 'Tinjau Resume Pulang' : '+ Buat Resume Kepulangan' }} &rarr;
                        </a>
                    </div>

                    @if($visit->discharge)
                        <div class="p-3.5 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2 text-xs">
                            <div class="flex items-center justify-between font-bold">
                                <span class="text-[var(--foreground)]">Disposisi: {{ ucfirst(str_replace('_', ' ', $visit->discharge->discharge_type)) }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                    {{ $visit->discharge->status }}
                                </span>
                            </div>
                            <p class="text-[var(--foreground-muted)]">
                                Anjuran: {{ $visit->discharge->activity_recommendation ?? 'Istirahat dan minum obat teratur.' }}
                            </p>
                            @if($visit->discharge->followUpPlans->count() > 0)
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-300 font-semibold">
                                    ✓ Follow-Up / Kontrol terjadwal ({{ $visit->discharge->followUpPlans->count() }} rencana)
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-6 text-center rounded-xl bg-[var(--surface-muted)] border border-dashed border-[var(--border)] space-y-2">
                            <p class="text-xs text-[var(--foreground-muted)]">Kunjungan belum memiliki rencana kepulangan / resume medis.</p>
                            <a href="{{ route('visits.discharge', $visit->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-500">
                                Proses Kepulangan & Handoff
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
