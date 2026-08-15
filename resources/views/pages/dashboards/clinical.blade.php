<x-app-layout>
    <x-slot name="title">Dashboard Klinis & Antrean Pelayanan</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="border-b border-[var(--border)] pb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] flex items-center gap-3">
                    <span class="p-2.5 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-xl shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                    Dashboard Klinis & Antrean Pelayanan
                </h1>
                <p class="mt-1.5 text-sm text-[var(--foreground-muted)]">
                    Pemantauan antrean kerja klinis real-time, status observasi, telaah konsultasi/rujukan, dan kontrol pasien.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('visits.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Registrasi Kunjungan
                </a>
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[var(--surface)] border border-[var(--border)] text-[var(--foreground)] hover:bg-[var(--surface-muted)] text-sm font-medium rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Laporan Klinis
                </a>
            </div>
        </div>

        <!-- Summary KPI Metrics Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Kunjungan Hari Ini</span>
                <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1.5">{{ $metrics['visits_today'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Pasien masuk hari ini</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Menunggu Pengkajian</span>
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1.5">{{ $metrics['waiting_assessment'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Antrean periksa dokter</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Observasi Aktif</span>
                <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1.5">{{ $metrics['under_observation'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Rawat istirahat poskestren</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Rujukan Berjalan</span>
                <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1.5">{{ $metrics['referral_active'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Sedang di faskes mitra</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Advice Menunggu Aksi</span>
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1.5">{{ $metrics['pending_consultations'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Perlu keputusan lokal</div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--foreground-muted)]">Kontrol / Follow-Up</span>
                <div class="text-2xl font-extrabold text-teal-600 dark:text-teal-400 mt-1.5">{{ $metrics['follow_up_due'] }}</div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">Jatuh tempo hari ini</div>
            </div>
        </div>

        <!-- Actionable Clinical Work Queues -->
        <div class="space-y-6">

            <!-- Section Header -->
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-[var(--foreground)] flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Antrean Tindakan Klinis (Work Queues)
                </h2>
                <span class="text-xs text-[var(--foreground-muted)]">Diurutkan berdasarkan prioritas & waktu kedatangan</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Work Queue 1: Waiting Assessment -->
                <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-[var(--border)] bg-amber-500/10 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-[var(--foreground)]">1. Menunggu Pengkajian Awal</h3>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            {{ $waitingAssessmentQueue->count() }} Pasien
                        </span>
                    </div>
                    <div class="p-3 divide-y divide-[var(--border)] flex-1">
                        @forelse($waitingAssessmentQueue as $item)
                            <div class="py-3 px-2 flex items-center justify-between gap-3 hover:bg-[var(--surface-muted)] rounded-xl transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm text-[var(--foreground)] truncate">{{ $item['patient_name'] }}</span>
                                        <span class="text-xs text-[var(--foreground-muted)] font-mono">RM: {{ $item['mrn'] }}</span>
                                    </div>
                                    <p class="text-xs text-[var(--foreground-muted)] mt-0.5 truncate">
                                        Keluhan: {{ $item['chief_complaint'] }}
                                    </p>
                                    <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">
                                        Tiba: {{ $item['waiting_time'] }} yang lalu ({{ $item['visit_number'] }})
                                    </div>
                                </div>
                                <a href="{{ $item['action_url'] }}" class="shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg shadow-xs transition">
                                    Mulai Periksa &rarr;
                                </a>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-[var(--foreground-muted)]">
                                Belum ada antrean pengkajian saat ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Work Queue 2: Active Observations -->
                <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-[var(--border)] bg-purple-500/10 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-[var(--foreground)]">2. Episode Observasi Rawat Istirahat</h3>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                            {{ $activeObservationQueue->count() }} Aktif
                        </span>
                    </div>
                    <div class="p-3 divide-y divide-[var(--border)] flex-1">
                        @forelse($activeObservationQueue as $item)
                            <div class="py-3 px-2 flex items-center justify-between gap-3 hover:bg-[var(--surface-muted)] rounded-xl transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm text-[var(--foreground)] truncate">{{ $item['patient_name'] }}</span>
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-[var(--surface-muted)] text-[var(--foreground)]">{{ $item['bed_label'] }}</span>
                                    </div>
                                    <div class="text-xs text-[var(--foreground-muted)] mt-0.5">
                                        Mulai: {{ $item['started_at']?->format('d M H:i') }} &bull; Interval: {{ $item['monitoring_interval'] }} mnt
                                    </div>
                                    <div class="text-[11px] text-purple-600 dark:text-purple-400 font-medium mt-0.5">
                                        Monitoring Berikutnya: {{ $item['next_monitoring_due_at']?->format('H:i') ?? 'Belum dijadwalkan' }}
                                    </div>
                                </div>
                                <a href="{{ $item['action_url'] }}" class="shrink-0 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg shadow-xs transition">
                                    Buka Lembar &rarr;
                                </a>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-[var(--foreground-muted)]">
                                Tidak ada santri yang sedang diobservasi.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Work Queue 3: Consultations Advice Pending Decision -->
                <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-[var(--border)] bg-rose-500/10 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-[var(--foreground)]">3. Saran Tele-Konsultasi Menunggu Aksi</h3>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                            {{ $pendingConsultationQueue->count() }} Kasus
                        </span>
                    </div>
                    <div class="p-3 divide-y divide-[var(--border)] flex-1">
                        @forelse($pendingConsultationQueue as $item)
                            <div class="py-3 px-2 flex items-center justify-between gap-3 hover:bg-[var(--surface-muted)] rounded-xl transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm text-[var(--foreground)] truncate">{{ $item['patient_name'] }}</span>
                                        <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded uppercase {{ $item['urgency'] === 'emergency' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">{{ $item['urgency'] }}</span>
                                    </div>
                                    <div class="text-xs text-[var(--foreground-muted)] mt-0.5">
                                        Dari: {{ $item['partner_name'] }}
                                    </div>
                                    <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">
                                        Diterima: {{ $item['responded_at']?->format('d M H:i') }}
                                    </div>
                                </div>
                                <a href="{{ $item['action_url'] }}" class="shrink-0 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-medium rounded-lg shadow-xs transition">
                                    Tinjau Advice &rarr;
                                </a>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-[var(--foreground-muted)]">
                                Tidak ada saran tele-konsultasi yang menunggu keputusan.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Work Queue 4: Referral Follow-Up & Return Review -->
                <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-[var(--border)] bg-indigo-500/10 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-[var(--foreground)]">4. Rujukan Eksternal & Telaah Kepulangan</h3>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                            {{ $referralFollowUpQueue->count() }} Kasus
                        </span>
                    </div>
                    <div class="p-3 divide-y divide-[var(--border)] flex-1">
                        @forelse($referralFollowUpQueue as $item)
                            <div class="py-3 px-2 flex items-center justify-between gap-3 hover:bg-[var(--surface-muted)] rounded-xl transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm text-[var(--foreground)] truncate">{{ $item['patient_name'] }}</span>
                                        <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-[var(--surface-muted)] text-[var(--foreground)]">{{ strtoupper($item['status']) }}</span>
                                    </div>
                                    <div class="text-xs text-[var(--foreground-muted)] mt-0.5">
                                        Faskes: {{ $item['partner_name'] }} ({{ $item['referral_number'] }})
                                    </div>
                                </div>
                                <a href="{{ $item['action_url'] }}" class="shrink-0 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg shadow-xs transition">
                                    Status &rarr;
                                </a>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-[var(--foreground-muted)]">
                                Tidak ada rujukan eksternal yang sedang berlangsung.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Work Queue 5: Follow-Up & Control Due (Full Width) -->
            <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-[var(--border)] bg-teal-500/10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <h3 class="text-sm font-bold text-[var(--foreground)]">5. Jadwal Kontrol & Follow-Up Jatuh Tempo</h3>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300">
                        {{ $dueFollowUpQueue->count() }} Jadwal
                    </span>
                </div>
                <div class="p-4">
                    @if($dueFollowUpQueue->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[var(--border)] text-sm text-left">
                                <thead class="text-xs font-semibold uppercase text-[var(--foreground-muted)] bg-[var(--surface-muted)]">
                                    <tr>
                                        <th class="py-2.5 px-3">Nama Pasien</th>
                                        <th class="py-2.5 px-3">Tipe Kontrol</th>
                                        <th class="py-2.5 px-3">Jatuh Tempo</th>
                                        <th class="py-2.5 px-3">Petunjuk / Tindakan</th>
                                        <th class="py-2.5 px-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--border)]">
                                    @foreach($dueFollowUpQueue as $item)
                                        <tr class="hover:bg-[var(--surface-muted)] transition">
                                            <td class="py-3 px-3 font-semibold text-[var(--foreground)]">
                                                {{ $item['patient_name'] }}
                                                <div class="text-xs text-[var(--foreground-muted)] font-normal">{{ $item['visit_number'] }}</div>
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="px-2 py-0.5 text-xs rounded bg-[var(--surface-muted)] text-[var(--foreground)]">
                                                    {{ str_replace('_', ' ', $item['follow_up_type']) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-xs">
                                                <span class="{{ $item['is_overdue'] ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-[var(--foreground-muted)]' }}">
                                                    {{ $item['due_at']?->format('d M Y H:i') }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-xs text-[var(--foreground-muted)]">
                                                {{ $item['instructions'] }}
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <a href="{{ $item['action_url'] }}" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-medium rounded-lg shadow-xs transition">
                                                    Buka Rekam &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-8 text-center text-xs text-[var(--foreground-muted)]">
                            Tidak ada jadwal kontrol yang jatuh tempo hari ini.
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
