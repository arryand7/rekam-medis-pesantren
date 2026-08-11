<x-app-layout>
    <x-slot name="title">Profil Rekam Medis Pasien — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Patient Context Header Component -->
        <x-patient-context-header :patient="$patient" />


        <!-- Grid Health Profile Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Col 1: Health Profile & Emergency Contacts -->
            <div class="space-y-6">
                <!-- Health Profile Overview -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Profil Kesehatan Dasar</h2>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                        <span class="text-xs font-semibold text-[var(--foreground-muted)]">Golongan Darah</span>
                        <span class="px-3 py-1 rounded-lg text-sm font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                            {{ $patient->healthProfile->blood_type ?? 'Belum terdata' }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span class="text-xs font-semibold text-[var(--foreground-muted)]">Catatan Darurat / Khusus:</span>
                        <p class="text-xs text-[var(--foreground)] p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] leading-relaxed">
                            {{ $patient->healthProfile->emergency_notes ?? 'Tidak ada catatan khusus.' }}
                        </p>
                    </div>
                </div>

                <!-- Emergency Contacts -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Kontak Darurat</h2>
                    <div class="space-y-2">
                        @forelse($patient->emergencyContacts as $contact)
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-xs text-[var(--foreground)]">{{ $contact->name }} ({{ $contact->relationship }})</div>
                                    <div class="text-xs text-[var(--foreground-muted)] font-mono mt-0.5">{{ $contact->phone }}</div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $contact->source }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada kontak darurat terdaftar.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Col 2: Structured Allergies & Medical Conditions -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Structured Allergies Table -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Alergi Terstruktur Pasien</h2>
                        <span class="text-xs text-[var(--foreground-muted)]">Non-destructive audit</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-[var(--foreground)]">
                            <thead class="bg-[var(--surface-muted)] text-[11px] uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                                <tr>
                                    <th class="px-4 py-2.5">Substansi / Allergen</th>
                                    <th class="px-4 py-2.5">Reaksi</th>
                                    <th class="px-4 py-2.5">Keparahan</th>
                                    <th class="px-4 py-2.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                @forelse($patient->allergies as $allergy)
                                    <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-[var(--foreground)]">
                                            {{ $allergy->allergen }}
                                        </td>
                                        <td class="px-4 py-3 text-[var(--foreground-muted)]">
                                            {{ $allergy->reaction ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 capitalize">
                                            {{ $allergy->severity ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($allergy->status === 'entered-in-error')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 line-through">
                                                    Entered in Error
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 capitalize">
                                                    {{ $allergy->status }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-[var(--foreground-muted)]">
                                            Tidak ada data alergi terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Medical Conditions Table -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Kondisi Medis Penting</h2>
                    <div class="space-y-2">
                        @forelse($patient->medicalConditions as $cond)
                            <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-xs text-[var(--foreground)]">{{ $cond->condition_name }}</div>
                                    <div class="text-xs text-[var(--foreground-muted)] mt-0.5">{{ $cond->notes ?? 'Tidak ada catatan' }}</div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300">
                                    {{ $cond->status }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-[var(--foreground-muted)]">Tidak ada kondisi medis terdaftar.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
