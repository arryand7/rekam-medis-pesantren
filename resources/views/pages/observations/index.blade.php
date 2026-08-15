<x-app-layout>
    <x-slot name="title">Daftar Observasi Pasien</x-slot>

    <div class="space-y-6">
        <!-- Page Title & Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Daftar Observasi Pasien (Poskestren Care)</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Pemantauan tirah baring/observasi berkala santri di Poskestren.</p>
            </div>
        </div>

        <!-- Active Observations Table -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--border)] font-bold text-sm text-[var(--foreground)] flex items-center justify-between">
                <span>Episode Observasi Aktif</span>
                <span class="text-xs text-[var(--foreground-muted)] font-normal">Pemantauan Berkala & Handover Shift</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Pasien & No. Kunjungan</th>
                            <th class="px-6 py-3.5">Lokasi & Bed</th>
                            <th class="px-6 py-3.5">Waktu Mulai</th>
                            <th class="px-6 py-3.5">Penanggung Jawab</th>
                            <th class="px-6 py-3.5">Jadwal Monitoring</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($episodes as $ep)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[var(--foreground)]">{{ $ep->medicalVisit->patient->person->name }}</div>
                                    <div class="text-xs text-[var(--foreground-muted)] font-mono">{{ $ep->medicalVisit->visit_number }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div class="font-semibold text-[var(--foreground)]">{{ $ep->location_label ?? 'Ruang Observasi' }}</div>
                                    <div class="text-[var(--foreground-muted)] font-mono">{{ $ep->bed_label ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                    <div class="font-semibold text-[var(--foreground)]">{{ $ep->started_at->format('H:i') }} WIB</div>
                                    <div>{{ $ep->started_at->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div class="font-semibold text-[var(--foreground)]">{{ $ep->responsibleOfficer->name ?? 'System' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($ep->isOverdue())
                                        <span class="inline-flex items-center gap-1 font-bold text-rose-600 dark:text-rose-400">
                                            ⚠️ Overdue (Perlu Monitoring)
                                        </span>
                                    @else
                                        <span class="text-[var(--foreground-muted)]">
                                            Jadwal: {{ $ep->next_monitoring_due_at ? $ep->next_monitoring_due_at->format('H:i').' WIB' : '-' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                        {{ $ep->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('observations.show', $ep->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors">
                                        Workspace Observasi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    Saat ini tidak ada santri/pasien yang sedang dalam episode observasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
