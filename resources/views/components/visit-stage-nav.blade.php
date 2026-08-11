@props(['visit', 'current' => 'overview'])

@php
    $hasVitals = $visit->vitalSigns->count() > 0 || $visit->latestVitalSign !== null;
    $hasAssessment = $visit->latestAssessment !== null || $visit->assessments->count() > 0;
    $hasMedication = $visit->medicationOrders->count() > 0;
    $hasConsultation = $visit->consultations->count() > 0;
    $hasReferral = ($visit->referrals->count() > 0) || ($visit->activeReferral !== null);
    $hasDischarge = $visit->discharge !== null || $visit->discharges->count() > 0;

    $stages = [
        [
            'id' => 'overview',
            'name' => 'Ringkasan Kunjungan',
            'route' => route('visits.show', $visit->id),
            'completed' => true,
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        ],
        [
            'id' => 'assessment',
            'name' => 'Tanda Vital & SOAP',
            'route' => route('visits.assessment', $visit->id),
            'completed' => $hasAssessment,
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        ],
        [
            'id' => 'medications',
            'name' => 'Resep & Obat',
            'route' => route('visits.medications.index', $visit->id),
            'completed' => $hasMedication,
            'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        ],
        [
            'id' => 'consultations',
            'name' => 'Tele-Konsultasi',
            'route' => route('visits.consultations.create', $visit->id),
            'completed' => $hasConsultation,
            'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        ],
        [
            'id' => 'referrals',
            'name' => 'Rujukan RS / Faskes',
            'route' => route('visits.referrals.create', $visit->id),
            'completed' => $hasReferral,
            'icon' => 'M13 5l7 7-7 7M5 5l7 7-7 7',
        ],
        [
            'id' => 'discharge',
            'name' => 'Kepulangan & Handoff',
            'route' => route('visits.discharge', $visit->id),
            'completed' => $hasDischarge,
            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        ],
    ];
@endphp

<div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl p-2 sm:p-3 shadow-xs mb-6 overflow-x-auto no-print">
    <nav class="flex items-center gap-2 min-w-max" aria-label="Tahapan Pelayanan Kunjungan">
        @foreach($stages as $index => $stage)
            @php
                $isActive = $current === $stage['id'];
            @endphp
            <a href="{{ $stage['route'] }}"
               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-150 {{ $isActive ? 'bg-[var(--primary)] text-white shadow-xs' : 'text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]' }}">
                <span class="flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-white/20 text-white' : ($stage['completed'] ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400' : 'bg-[var(--surface-muted)] text-[var(--foreground-muted)]') }}">
                    @if($stage['completed'] && !$isActive)
                        ✓
                    @else
                        {{ $index + 1 }}
                    @endif
                </span>
                <span>{{ $stage['name'] }}</span>
            </a>

            @if(!$loop->last)
                <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            @endif
        @endforeach
    </nav>
</div>
