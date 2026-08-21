@props(['patient', 'visit' => null])

@php
    $person = $patient->person;
    $activeAllergies = $patient->activeAllergies ?? collect();
    $initials = strtoupper(mb_substr($person->name ?? 'P', 0, 1));
    if (str_contains($person->name ?? '', ' ')) {
        $parts = explode(' ', trim($person->name ?? ''));
        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    }
    $userType = ucfirst(str_replace('_', ' ', $person->user_type ?? 'santri'));
    $gender = match($person->gender ?? '') {
        'L', 'male' => 'Laki-laki',
        'P', 'female' => 'Perempuan',
        default => '-'
    };
    $photoUrl = $person->photo_url ?? null;
@endphp

<div class="bg-[var(--surface)] p-5 sm:p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Patient Identity Info -->
        <div class="flex items-start gap-4">
            <!-- Foto Profil atau Avatar Inisial -->
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl shrink-0 overflow-hidden border-2 shadow-sm
                {{ $photoUrl ? 'border-sky-300/60 dark:border-sky-600/60' : 'border-sky-500/20 bg-sky-500/10' }}">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}"
                         alt="Foto {{ $person->name }}"
                         class="w-full h-full object-cover"
                         loading="lazy"
                         onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-sky-500/10 text-sky-600 dark:text-sky-400 font-bold text-xl\'>{{ $initials }}</div>'">
                @else
                    <div class="w-full h-full flex items-center justify-center text-sky-600 dark:text-sky-400 font-bold text-xl">
                        {{ $initials }}
                    </div>
                @endif
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl sm:text-2xl font-bold text-[var(--foreground)] tracking-tight">{{ $person->name ?? 'Nama Pasien' }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300">
                        {{ $patient->patient_number }}
                    </span>
                    @if($patient->is_eligible)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Layak
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--foreground-muted)] mt-1.5 font-medium">
                    <span>Tipe: <strong class="text-[var(--foreground)]">{{ $userType }}</strong></span>
                    <span>•</span>
                    <span>Gender: <strong class="text-[var(--foreground)]">{{ $gender }}</strong></span>
                    <span>•</span>
                    <span>NIS/NIP: <strong class="text-[var(--foreground)] font-mono">{{ $person->nis_nip ?? '-' }}</strong></span>
                    @if($visit)
                        <span>•</span>
                        <span>No. Kunjungan: <strong class="text-[var(--primary)] font-mono">{{ $visit->visit_number }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-[var(--border)]">
            <a href="{{ route('patients.show', $patient->id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--border)] transition-colors"
               title="Buka Profil Lengkap & Rekam Medis Pasien">
                <svg class="w-4 h-4 text-[var(--foreground-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profil Lengkap</span>
            </a>

            @if(!$visit)
                <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Daftarkan Kunjungan</span>
                </a>
            @else
                <a href="{{ route('visits.show', $visit->id) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-sky-50 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 hover:bg-sky-100 transition-colors">
                    <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Workspace Kunjungan</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Allergy Warning Banner -->
    @if($activeAllergies->count() > 0)
        <div class="p-3.5 rounded-xl bg-amber-500/10 border-l-4 border-amber-500 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2 text-amber-800 dark:text-amber-200 font-bold">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>PERINGATAN ALERGI AKTIF:</span>
            </div>
            <div class="flex flex-wrap gap-1.5">
                @foreach($activeAllergies as $allergy)
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-amber-200 dark:bg-amber-900/80 text-amber-900 dark:text-amber-100 border border-amber-300 dark:border-amber-700">
                        ⚠️ {{ $allergy->allergen }} ({{ $allergy->reaction ?? 'Reaksi tidak disebutkan' }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
