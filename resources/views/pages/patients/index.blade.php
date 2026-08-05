<x-app-layout>
    <x-slot name="title">Kelayakan Pasien — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Kelayakan & Profil Pasien</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Semua manusia (Santri, Guru, Staf, Pengasuh, Admin) berhak memiliki profil pasien di Poskestren.</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900">
                    Patient Eligibility Rules Active
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Nomor Pasien</th>
                            <th class="px-6 py-3.5">Nama Manusia (Person)</th>
                            <th class="px-6 py-3.5">Tipe Pengguna</th>
                            <th class="px-6 py-3.5">Status Kelayakan</th>
                            <th class="px-6 py-3.5">Golongan Darah</th>
                            <th class="px-6 py-3.5">Kontak Darurat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($patients as $patient)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-[var(--primary)]">
                                    {{ $patient->patient_number }}
                                </td>
                                <td class="px-6 py-4 font-medium text-[var(--foreground)]">
                                    {{ $patient->person->name ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="px-2 py-1 rounded bg-[var(--surface-muted)] text-[var(--foreground-muted)] font-medium">
                                        {{ ucfirst($patient->person->user_type ?? 'santri') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($patient->is_eligible)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Layak (Eligible)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 dark:text-rose-400">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Non-Eligible: {{ $patient->ineligibility_reason }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-[var(--foreground)]">
                                    {{ $patient->blood_type ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div>{{ $patient->emergency_contact_name ?? '-' }}</div>
                                    <div class="text-[var(--foreground-muted)]">{{ $patient->emergency_contact_phone ?? '' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    Belum ada profil Pasien terbuat. Pasien akan dibuat secara otomatis atau saat pendaftaran pertama.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
