<x-app-layout>
    <x-slot name="title">Konsultasi Klinis Eksternal — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--foreground)]">Konsultasi Klinis Eksternal</h1>
                <p class="text-xs text-[var(--foreground-muted)] mt-1">Komunikasi Profesional (Dokter Poskestren ke Dokter Puskesmas/RS Mitra).</p>
            </div>
            <div>
                <a href="{{ route('healthcare-partners.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--border)]">
                    Kelola Faskes Mitra &rarr;
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-700 dark:text-emerald-300 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Consultation List Table -->
        <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs overflow-hidden">
            <div class="p-6 border-b border-[var(--border)] flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Daftar Konsultasi Eksternal</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-[var(--foreground-muted)] font-bold uppercase tracking-wider border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3">Tanggal / Waktu</th>
                            <th class="px-6 py-3">Pasien & No. Kunjungan</th>
                            <th class="px-6 py-3">Faskes Mitra</th>
                            <th class="px-6 py-3">Tujuan Konsultasi</th>
                            <th class="px-6 py-3">Tingkat Urgensi</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)] font-medium">
                        @forelse($consultations as $c)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-[var(--foreground-muted)]">
                                    {{ $c->created_at->format('d M Y, H:i') }} WIB
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[var(--foreground)]">{{ $c->medicalVisit->patient->person->name }}</div>
                                    <div class="font-mono text-[11px] text-[var(--foreground-muted)]">{{ $c->medicalVisit->visit_number }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-[var(--foreground)]">{{ $c->partner->name }}</div>
                                    <div class="text-[11px] text-[var(--foreground-muted)]">{{ $c->recipientContact->name ?? 'Dokter Jaga' }}</div>
                                </td>
                                <td class="px-6 py-4 max-w-xs truncate">
                                    {{ $c->purpose }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $c->urgency === 'emergency' ? 'bg-rose-100 text-rose-700' : ($c->urgency === 'urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ $c->urgency }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('consultations.show', $c->id) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)]">
                                        Detail & Respons &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-xs text-[var(--foreground-muted)]">
                                    Belum ada catatan konsultasi klinis eksternal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--border)]">
                {{ $consultations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
