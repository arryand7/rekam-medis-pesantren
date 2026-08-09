<x-app-layout>
    <x-slot name="title">Workspace Kepulangan & Penutupan Kunjungan {{ $visit->visit_number }} — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <a href="{{ route('visits.show', $visit->id) }}" class="text-sm text-sky-600 dark:text-sky-400 hover:underline">← Detail Kunjungan</a>
                <div class="mt-2 flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kepulangan Klinis & Penutupan Kunjungan</h1>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
                        {{ $visit->visit_number }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ ucfirst(str_replace('_', ' ', $visit->status)) }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pasien: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $visit->patient?->person?->full_name }}</span> ({{ $visit->patient?->patient_number }}) |
                    Keluhan: <span class="text-gray-700 dark:text-gray-300">{{ $visit->chief_complaint }}</span>
                </p>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <!-- Readiness Evaluation Banner -->
        @if(! $readiness['is_ready'])
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-5 border border-red-200 dark:border-red-800 space-y-2">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-300 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Prasyarat Teknis Belum Terpenuhi (Blockers)</span>
                </div>
                <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                    @foreach($readiness['technical_blockers'] as $blocker)
                        <li>{{ $blocker }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="rounded-xl bg-sky-50 dark:bg-sky-900/20 p-4 border border-sky-200 dark:border-sky-800 flex items-center justify-between">
                <div class="flex items-center gap-2 text-sky-800 dark:text-sky-300 text-sm font-medium">
                    <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Seluruh prasyarat klinis & teknis terpenuhi. Kunjungan siap difinalisasi untuk kepulangan.</span>
                </div>
            </div>
        @endif

        @if(! empty($readiness['warnings']))
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 p-4 border border-amber-200 dark:border-amber-800 space-y-1">
                <div class="text-xs font-semibold text-amber-800 dark:text-amber-300">Catatan & Peringatan Klinis:</div>
                <ul class="list-disc list-inside text-xs text-amber-700 dark:text-amber-400 space-y-1">
                    @foreach($readiness['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Form Preparation -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Discharge Form Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Formulir Kepulangan Klinis</h2>

                    @php
                        $discharge = $visit->discharge;
                    @endphp

                    <form action="{{ route('visits.discharge.store', $visit->id) }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Kepulangan <span class="text-red-500">*</span></label>
                                <select name="discharge_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" required>
                                    <option value="return_to_activity" @selected(old('discharge_type', $discharge?->discharge_type) === 'return_to_activity')>Kembali Beraktivitas (Return to Activity)</option>
                                    <option value="rest_required" @selected(old('discharge_type', $discharge?->discharge_type) === 'rest_required')>Perlu Istirahat (Rest Required)</option>
                                    <option value="continue_poskestren_care" @selected(old('discharge_type', $discharge?->discharge_type) === 'continue_poskestren_care')>Lanjut Perawatan Poskestren</option>
                                    <option value="follow_up_external" @selected(old('discharge_type', $discharge?->discharge_type) === 'follow_up_external')>Kontrol Fasilitas Eksternal</option>
                                    <option value="referred_again" @selected(old('discharge_type', $discharge?->discharge_type) === 'referred_again')>Rujukan Ulang</option>
                                    <option value="transfer_of_care" @selected(old('discharge_type', $discharge?->discharge_type) === 'transfer_of_care')>Alih Rawat / Dijemput Wali</option>
                                    <option value="other" @selected(old('discharge_type', $discharge?->discharge_type) === 'other')>Lainnya</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Destinasi Pulang <span class="text-red-500">*</span></label>
                                <input type="text" name="discharge_destination" value="{{ old('discharge_destination', $discharge?->discharge_destination ?? 'Asrama Santri') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="Contoh: Asrama Al-Farabi, Rumah Orang Tua, Kelas" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kondisi Akhir Saat Pulang <span class="text-red-500">*</span></label>
                            <input type="text" name="final_condition" value="{{ old('final_condition', $discharge?->final_condition ?? 'Membaik, keluhan mereda, tanda vital stabil') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" required>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Ringkasan Klinis Kepulangan <span class="text-red-500">*</span></label>
                            <textarea name="clinical_summary" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="Ringkasan evaluasi medis, penanganan yang telah diberikan, dan kondisi saat dipulangkan..." required>{{ old('clinical_summary', $discharge?->clinical_summary) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Rekomendasi Aktivitas <span class="text-red-500">*</span></label>
                                <select name="activity_recommendation" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" required>
                                    <option value="full_activity" @selected(old('activity_recommendation', $discharge?->activity_recommendation) === 'full_activity')>Aktivitas Penuh / Normal</option>
                                    <option value="limited_activity" @selected(old('activity_recommendation', $discharge?->activity_recommendation) === 'limited_activity')>Aktivitas Terbatas / Ringan</option>
                                    <option value="rest" @selected(old('activity_recommendation', $discharge?->activity_recommendation) === 'rest')>Istirahat Total (Bed Rest)</option>
                                    <option value="temporarily_not_cleared" @selected(old('activity_recommendation', $discharge?->activity_recommendation) === 'temporarily_not_cleared')>Belum Diizinkan Aktivitas Berat</option>
                                    <option value="other" @selected(old('activity_recommendation', $discharge?->activity_recommendation) === 'other')>Lainnya</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Anjuran Istirahat</label>
                                <input type="text" name="rest_recommendation" value="{{ old('rest_recommendation', $discharge?->rest_recommendation) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="Contoh: Istirahat di asrama selama 1x24 jam">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Batasan & Pantangan</label>
                            <input type="text" name="restriction_notes" value="{{ old('restriction_notes', $discharge?->restriction_notes) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="Contoh: Hindari olahraga berat, minum air putih cukup">
                        </div>

                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700 space-y-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="follow_up_required" name="follow_up_required" value="1" @checked(old('follow_up_required', $discharge?->follow_up_required)) class="rounded border-gray-300 text-sky-600">
                                <label for="follow_up_required" class="text-xs font-medium text-gray-700 dark:text-gray-300">Memerlukan Rencana Tindak Lanjut / Kontrol Ulang (Follow-Up)</label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Ringkasan Follow-Up</label>
                                    <input type="text" name="follow_up_summary" value="{{ old('follow_up_summary', $discharge?->follow_up_summary) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" placeholder="Contoh: Kontrol ulang jika demam berulang">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Target Waktu Kontrol</label>
                                    <input type="datetime-local" name="follow_up_date" value="{{ old('follow_up_date', $discharge?->follow_up_date?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-medium shadow-sm transition">
                                Simpan Draf Kepulangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Col: Summary & Finalization Action -->
            <div class="space-y-6">
                <!-- Action Panel -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Status Kepulangan</h3>

                    @if($discharge)
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-xs space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Status Draf:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ strtoupper($discharge->status) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Disiapkan Oleh:</span>
                                <span class="text-gray-800 dark:text-gray-200">{{ $discharge->preparedBy?->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Waktu Draf:</span>
                                <span class="text-gray-800 dark:text-gray-200">{{ $discharge->prepared_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                        </div>

                        @if($discharge->status === 'draft' && $readiness['is_ready'])
                            <form action="{{ route('discharges.finalize', $discharge->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi kepulangan ini? Kunjungan medis akan ditutup secara permanen.')">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Finalisasi & Tutup Kunjungan
                                </button>
                            </form>
                        @elseif($discharge->isFinalized())
                            <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/30 p-3 text-xs text-emerald-800 dark:text-emerald-300 font-medium">
                                ✓ Kunjungan telah resmi ditutup pada {{ $discharge->finalized_at?->format('d/m/Y H:i') }}.
                            </div>
                            <a href="{{ route('discharges.show', $discharge->id) }}" class="block w-full py-2 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-center rounded-lg text-xs font-semibold">
                                Lihat Dokumen & Riwayat Versi
                            </a>
                        @endif
                    @else
                        <p class="text-xs text-gray-500 dark:text-gray-400">Draf kepulangan belum disimpan. Isi formulir di sebelah kiri untuk memulai.</p>
                    @endif
                </div>

                <!-- Clinical Reference Box -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                    <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Ringkasan Medis Kunjungan</h3>
                    <div class="text-xs space-y-2">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 block">Diagnosis Kerja:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $visit->latestAssessment?->working_diagnosis ?? 'Belum ada' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 block">Rekomendasi Disposisi:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $visit->latestAssessment?->disposition_recommendation ?? 'Belum ada' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
