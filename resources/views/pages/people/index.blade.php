<x-app-layout>
    <x-slot name="title">Direktori Person — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Direktori Person (Identitas Manusia)</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Daftar manusia terdaftar di lingkungan SABIRA (Santri, Guru, Staf, Pengasuh, Petugas Kesehatan, Admin).</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)]">
                    Phase 1 Identity Management
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Nama & Gate ID</th>
                            <th class="px-6 py-3.5">NIS / NIP / NIK</th>
                            <th class="px-6 py-3.5">Tipe Pengguna</th>
                            <th class="px-6 py-3.5">Status Gate</th>
                            <th class="px-6 py-3.5">Kelayakan Pasien</th>
                            <th class="px-6 py-3.5">Terakhir Sinkron</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($people as $person)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-[var(--foreground)]">{{ $person->name }}</div>
                                    <div class="text-xs text-[var(--foreground-muted)] font-mono">{{ $person->gate_user_id ?? 'Belum Terhubung Gate' }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">
                                    <div>NIS/NIP: {{ $person->nis_nip ?? '-' }}</div>
                                    <div class="text-[var(--foreground-muted)]">NIK: {{ $person->nik ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300">
                                        {{ ucfirst($person->user_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($person->source_status === 'active')
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 dark:text-amber-400">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($person->isHumanPatientEligible())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                            Eligible
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300">
                                            Non-Eligible
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                    {{ $person->synced_at ? $person->synced_at->diffForHumans() : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    Belum ada data Person terdaftar. Jalankan seed atau Gate Dry-Run Sync.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($people, 'links'))
                <div class="px-6 py-4 border-t border-[var(--border)]">
                    {{ $people->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
