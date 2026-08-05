<x-app-layout>
    <x-slot name="title">Kunjungan Medis & Antrean Intake — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Page Title & Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Kunjungan Medis (Intake Queue)</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Daftar registrasi kedatangan dan antrean pelayanan medis Poskestren.</p>
            </div>
            <div>
                <a href="{{ route('visits.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Registrasi Kunjungan Baru
                </a>
            </div>
        </div>

        <!-- Visits Table -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">No. Kunjungan</th>
                            <th class="px-6 py-3.5">Pasien</th>
                            <th class="px-6 py-3.5">Waktu Kedatangan</th>
                            <th class="px-6 py-3.5">Keluhan Utama</th>
                            <th class="px-6 py-3.5">Pengantar / Asal</th>
                            <th class="px-6 py-3.5">Status Intake</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($visits as $visit)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-[var(--primary)]">
                                    {{ $visit->visit_number }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('patients.show', $visit->patient_id) }}" class="font-bold text-[var(--foreground)] hover:underline">
                                        {{ $visit->patient->person->name }}
                                    </a>
                                    <div class="text-xs text-[var(--foreground-muted)] font-mono">{{ $visit->patient->patient_number }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                    <div class="font-semibold text-[var(--foreground)]">{{ $visit->arrived_at->format('H:i:s') }} WIB</div>
                                    <div>{{ $visit->arrived_at->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground)] max-w-xs truncate">
                                    {{ $visit->chief_complaint }}
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                    <div class="capitalize font-medium text-[var(--foreground)]">{{ str_replace('_', ' ', $visit->reporting_type) }}</div>
                                    <div>{{ $visit->origin_location ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $badgeClass = match($visit->status) {
                                            'registered' => 'bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300',
                                            'waiting_assessment' => 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300',
                                            'cancelled' => 'bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300',
                                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $visit->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('visits.show', $visit->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--border)] transition-colors">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    Belum ada kunjungan medis tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
