<x-app-layout>
    <x-slot name="title">Workspace Observasi — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Patient Context Header -->
        <x-patient-context-header :patient="$episode->medicalVisit->patient" :visit="$episode->medicalVisit" />

        <!-- Visit Stage Stepper Navigation -->
        <x-visit-stage-nav :visit="$episode->medicalVisit" current="observations" />

        <!-- Observation Episode Details Banner -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-lg border border-purple-500/20 shrink-0">
                    🛏️
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-bold text-[var(--foreground)] tracking-tight">Episode Observasi Rawat Inap Poskestren</h1>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono {{ $episode->isActive() ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                            {{ $episode->status }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-[var(--foreground-muted)] mt-1 font-medium">
                        <span>Lokasi Bed: <strong class="text-[var(--foreground)]">{{ $episode->location_label }} ({{ $episode->bed_label ?? '-' }})</strong></span>
                        <span>•</span>
                        <span>PJ Aktif: <strong class="text-[var(--primary)]">{{ $episode->responsibleOfficer->name ?? 'System' }}</strong></span>
                        <span>•</span>
                        <span>Mulai: <strong class="text-[var(--foreground)]">{{ $episode->created_at?->format('d M Y, H:i') }} WIB</strong></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2" x-data="{ openHandover: false, openComplete: false }">
                @if($episode->isActive())
                    <button @click="openHandover = true" class="px-3 py-2 rounded-xl text-xs font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                        Handover Shift
                    </button>
                    <button @click="openComplete = true" class="px-3 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                        Selesaikan Observasi
                    </button>
                @else
                    <span class="px-3 py-2 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground-muted)] border border-[var(--border)]">
                        Observasi Selesai (Read-Only)
                    </span>
                @endif


                <!-- Modal Handover Shift -->
                <div x-show="openHandover" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" x-cloak>
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] max-w-lg w-full space-y-4 shadow-xl">
                        <h3 class="text-lg font-bold text-[var(--foreground)]">Pengajuan Handover Shift Jaga</h3>
                        <p class="text-xs text-[var(--foreground-muted)]">Penanggung jawab observasi akan berpindah ke petugas penerima setelah handover disetujui (acknowledged).</p>

                        <form action="{{ route('observations.handover.store', $episode->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Petugas Penerima (Tujuan Handover)</label>
                                <select name="to_user_id" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                    <option value="">-- Pilih Petugas Jaga Berikutnya --</option>
                                    @foreach($medicalUsers as $mu)
                                        <option value="{{ $mu->id }}">{{ $mu->name }} ({{ $mu->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Ringkasan Perkembangan Santri <span class="text-rose-500">*</span></label>
                                <textarea name="summary" required rows="2" placeholder="Jelaskan kondisi santri selama observasi shift ini..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Kondisi Saat Ini <span class="text-rose-500">*</span></label>
                                <input type="text" name="current_condition" required placeholder="Contoh: Suhu 37.2°C, keluhan pusing berkurang..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Tugas / Pesan Pemantauan Lanjutan</label>
                                <textarea name="pending_tasks" rows="2" placeholder="Contoh: Cek suhu jam 18:00, ingatkan santri minum air hangat..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="openHandover = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[var(--primary)] text-white">
                                    Kirim Handover Shift
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Complete Observation -->
                <div x-show="openComplete" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" x-cloak>
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] max-w-md w-full space-y-4 shadow-xl">
                        <h3 class="text-lg font-bold text-[var(--foreground)]">Penyelesaian Episode Observasi</h3>
                        <p class="text-xs text-[var(--foreground-muted)]">Tentukan rekomendasi akhir setelah santri menjalani masa observasi di Poskestren.</p>

                        <form action="{{ route('observations.complete', $episode->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Outcome / Hasil Observasi <span class="text-rose-500">*</span></label>
                                <select name="outcome" required class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                    <option value="return_to_activity_recommended">Kondisi Membaik — Direkomendasikan Kembali Beraktivitas</option>
                                    <option value="rest_recommended">Diizinkan Istirahat Lanjutan di Asrama</option>
                                    <option value="external_consultation_recommended">Perlu Konsultasi ke Puskesmas / Faskes Luar</option>
                                    <option value="referral_recommended">Rujukan Faskes Lanjutan / Rumah Sakit</option>
                                    <option value="emergency_referral_recommended">⚠️ RUJUKAN DARURAT (EMERGENCY)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-[var(--foreground-muted)] mb-1">Alasan / Catatan Akhir Observasi <span class="text-rose-500">*</span></label>
                                <textarea name="outcome_reason" required rows="3" placeholder="Jelaskan dasar pertimbangan hasil observasi..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="openComplete = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[var(--foreground-muted)]">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white">
                                    Selesaikan Observasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-700 dark:text-emerald-300 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Grid 2 Cols: Monitoring Form & Timeline -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Form Add Monitoring Record -->
            <div class="space-y-6">
                @if($episode->isActive())
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Tambah Lembar Monitoring Berkala</h2>

                        <form action="{{ route('observations.monitoring.store', $episode->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Ringkasan Kondisi Santri <span class="text-rose-500">*</span></label>
                                <textarea name="condition_summary" required rows="2" placeholder="Catat keluhan saat ini, respon istirahat, dll..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Perubahan Gejala</label>
                                <input type="text" name="symptom_changes" placeholder="Contoh: Demam mulai turun setelah kompres..." class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[var(--foreground-muted)] mb-1">Kondisi Umum</label>
                                <select name="general_condition" class="w-full px-3 py-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-xs text-[var(--foreground)]">
                                    <option value="good">Baik / Stabil</option>
                                    <option value="moderate">Cukup / Perlu Dipantau</option>
                                    <option value="weak">Lemas / Membutuhkan Perhatian</option>
                                    <option value="critical">Kritis / Darurat</option>
                                </select>
                            </div>

                            <div class="pt-2 text-right">
                                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors">
                                    Catat Monitoring Berkala
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-2 text-center">
                        <div class="text-2xl">🔒</div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--foreground)]">Observasi Telah Ditutup</h3>
                        <p class="text-xs text-[var(--foreground-muted)] leading-relaxed">Episode observasi ini berstatus <strong>{{ $episode->status }}</strong> dan terkunci. Seluruh catatan observasi bersifat arsip permanen rekam medis.</p>
                    </div>
                @endif
            </div>


            <!-- Timeline Monitoring Records & Handovers -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Handover Submissions Table (If any) -->
                @if($episode->handovers->count() > 0)
                    <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Riwayat Handover Shift Jaga</h2>
                        <div class="space-y-3">
                            @foreach($episode->handovers as $ho)
                                <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-[var(--foreground)]">Handover dari {{ $ho->fromUser->name ?? 'System' }}</span>
                                        <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold uppercase {{ $ho->status === 'acknowledged' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $ho->status }}
                                        </span>
                                    </div>
                                    <p class="text-[var(--foreground)] font-medium">{{ $ho->summary }}</p>
                                    <div class="text-[var(--foreground-muted)] font-mono text-[11px]">
                                        Tugas Lanjutan: {{ $ho->pending_tasks ?? '-' }}
                                    </div>
                                    @if($ho->status === 'submitted')
                                        <div class="pt-2">
                                            <form action="{{ route('observations.handover.acknowledge', $ho->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700">
                                                    Konfirmasi Handover & Ambil Alih Tugas PJ
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Periodic Monitoring Logs -->
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Lembar Pemantauan Berkala (Periodic Monitoring Logs)</h2>
                    <div class="space-y-3">
                        @forelse($episode->records as $rec)
                            <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="font-bold text-[var(--foreground)] flex items-center gap-2">
                                        <span>{{ $rec->recorded_at->format('H:i:s') }} WIB</span>
                                        <span class="text-[var(--foreground-muted)] font-normal">({{ $rec->recorded_at->format('d M Y') }})</span>
                                    </div>
                                    <span class="text-[var(--foreground-muted)]">Petugas: {{ $rec->recordedBy->name ?? 'System' }}</span>
                                </div>
                                <p class="text-[var(--foreground)] font-medium leading-relaxed">{{ $rec->condition_summary }}</p>
                                @if($rec->symptom_changes)
                                    <div class="text-[var(--foreground-muted)] font-mono text-[11px]">
                                        Perubahan Gejala: {{ $rec->symptom_changes }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-[var(--foreground-muted)]">Belum ada lembar pemantauan berkala tercatat.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
