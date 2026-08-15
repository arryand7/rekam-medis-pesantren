<x-app-layout>
    <x-slot name="title">Data Rekam Medis Pasien</x-slot>

    <div class="space-y-6">
        <!-- Header Card -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Direktori Rekam Medis Pasien</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Daftar seluruh santri, ustadz, dan pengasuh dengan profil rekam medis aktif di POSKESTREN.</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Universal Patient Eligibility Active
                </span>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-[var(--surface)] p-4 rounded-2xl border border-[var(--border)] shadow-xs">
            <form method="GET" action="{{ route('patients.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari berdasarkan nama, No. Pasien (MRN), NIK, NIS/NIP..."
                           class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-sm text-[var(--foreground)] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-colors">
                    @if(request('search'))
                        <a href="{{ route('patients.index') }}"
                           class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                           title="Hapus filter pencarian">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cari Pasien</span>
                    </button>

                    @if(request('search'))
                        <a href="{{ route('patients.index') }}"
                           class="px-3.5 py-2.5 rounded-xl text-xs font-medium text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] border border-[var(--border)]">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            @if(request('search'))
                <div class="mt-3 text-xs text-[var(--foreground-muted)] flex items-center justify-between">
                    <span>Hasil pencarian untuk kata kunci: <strong class="text-[var(--foreground)]">"{{ request('search') }}"</strong></span>
                    <span>Ditemukan {{ $patients->total() }} data</span>
                </div>
            @endif
        </div>

        <!-- Table Container -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Nomor Pasien</th>
                            <th class="px-6 py-3.5">Nama & Alergi</th>
                            <th class="px-6 py-3.5">Tipe & Identitas</th>
                            <th class="px-6 py-3.5">Status Kelayakan</th>
                            <th class="px-6 py-3.5">Gol. Darah</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($patients as $patient)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-[var(--primary)]">
                                    {{ $patient->patient_number }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[var(--foreground)]">
                                        <a href="{{ route('patients.show', $patient->id) }}" class="hover:underline text-[var(--foreground)]">
                                            {{ $patient->person->name ?? 'Unknown' }}
                                        </a>
                                    </div>
                                    @if($patient->activeAllergies->count() > 0)
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($patient->activeAllergies as $allergy)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-900">
                                                    ⚠️ {{ $allergy->allergen }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--foreground-muted)] font-semibold uppercase text-[10px] border border-[var(--border)]">
                                        {{ ucfirst($patient->person->user_type ?? 'santri') }}
                                    </span>
                                    <div class="text-[11px] text-[var(--foreground-muted)] font-mono mt-1">
                                        NIS/NIP: {{ $patient->person->nis_nip ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($patient->is_eligible)
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600 dark:text-emerald-400">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Layak Pelayanan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 font-semibold text-rose-600 dark:text-rose-400">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Non-Eligible
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-[var(--foreground)]">
                                    {{ $patient->healthProfile->blood_type ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('patients.show', $patient->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--border)] transition-colors">
                                            <span>Buka Profil</span>
                                        </a>

                                        <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors shadow-xs"
                                           title="Daftarkan Kunjungan Baru">
                                            <span>+ Kunjungan</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-[var(--foreground-muted)]">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <div class="w-12 h-12 rounded-2xl bg-[var(--surface-muted)] text-slate-400 mx-auto flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        @if(request('search'))
                                            <div class="text-sm font-bold text-[var(--foreground)]">Data Pasien Tidak Ditemukan</div>
                                            <p class="text-xs">Tidak ada data pasien yang sesuai dengan kata kunci pencarian "{{ request('search') }}".</p>
                                            <div>
                                                <a href="{{ route('patients.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--border)]">
                                                    Reset Pencarian
                                                </a>
                                            </div>
                                        @else
                                            <div class="text-sm font-bold text-[var(--foreground)]">Belum Ada Data Pasien</div>
                                            <p class="text-xs">Profil pasien akan dibuat secara otomatis saat registrasi identitas atau pendaftaran kunjungan pertama.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            @if($patients->hasPages())
                <div class="p-4 border-t border-[var(--border)] bg-[var(--surface-muted)]/30">
                    {{ $patients->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
