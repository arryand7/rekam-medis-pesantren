<x-app-layout>
    <x-slot name="title">Buat Rujukan — {{ $visit->patient->person->name }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="ui-card rounded-2xl p-6 shadow-xs">
            <a href="{{ route('visits.show', $visit->id) }}" class="ui-link text-sm font-medium hover:underline">← Kembali ke Kunjungan</a>
            <h1 class="mt-2 text-2xl font-bold text-[var(--foreground)]">Pembuatan Rujukan Eksternal</h1>
            <p class="mt-1 text-sm ui-text-muted">
                Kunjungan: <span class="font-mono font-semibold">{{ $visit->visit_number }}</span> |
                Pasien: <span class="font-semibold">{{ $visit->patient->person->name }}</span>
            </p>
        </div>

        @if($visit->latestAssessment?->status !== 'finalized')
            <div class="ui-banner-danger rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-[var(--danger)] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold">Pengkajian Klinis Belum Difinalisasi</h3>
                        <p class="mt-1 text-sm">Rujukan memerlukan pengkajian klinis yang telah difinalisasi oleh petugas berwenang.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Emergency Warning Banner -->
        <div class="ui-banner-warning rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-[var(--warning)] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-bold">⚠ Rujukan Darurat Tidak Perlu Menunggu Konsultasi</h3>
                    <p class="mt-1 text-sm">
                        Untuk kondisi darurat, pilih urgensi <strong>Emergency</strong>. Konsultasi yang sedang berjalan tidak perlu diselesaikan terlebih dahulu.
                        Rujukan darurat tidak boleh tertahan oleh proses konsultasi atau persetujuan administratif.
                    </p>
                </div>
            </div>
        </div>

        <!-- Referral Form -->
        <form method="POST" action="{{ route('visits.referrals.store', $visit->id) }}" class="space-y-6">
            @csrf

            @if ($errors->any())
                <div class="ui-banner-danger rounded-xl p-4">
                    <ul class="text-sm list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="ui-card shadow-sm rounded-xl p-6 space-y-5">
                <h2 class="text-base font-semibold text-[var(--foreground)] border-b border-[var(--border-soft)] pb-3">Informasi Rujukan</h2>

                <!-- Urgency -->
                <div>
                    <label class="block text-sm font-medium ui-text-secondary mb-2">
                        Tingkat Urgensi <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-3">
                        @foreach(['routine' => ['label' => 'Rutin', 'tone' => 'success'], 'urgent' => ['label' => 'Urgent', 'tone' => 'warning'], 'emergency' => ['label' => 'Darurat / Emergency', 'tone' => 'danger']] as $val => $opt)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="urgency" value="{{ $val }}" {{ old('urgency') === $val ? 'checked' : ($val === 'routine' && !old('urgency') ? 'checked' : '') }} class="ui-choice-input sr-only">
                                <div data-tone="{{ $opt['tone'] }}" class="ui-choice-card rounded-lg p-3 text-center transition-all">
                                    <span class="block text-sm font-semibold">{{ $opt['label'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Destination Partner -->
                <div>
                    <label for="healthcare_partner_id" class="block text-sm font-medium ui-text-secondary mb-1">
                        Fasilitas Kesehatan Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select id="healthcare_partner_id" name="healthcare_partner_id" required
                            class="ui-form-control w-full rounded-lg border shadow-sm focus:ring-[var(--focus-ring)] focus:border-[var(--primary)]">
                        <option value="">— Pilih Fasilitas Kesehatan —</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('healthcare_partner_id') === $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }} ({{ ucfirst($partner->partner_type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Requested Service -->
                <div>
                    <label for="requested_service_or_department" class="block text-sm font-medium ui-text-secondary mb-1">
                        Layanan / Poli yang Diminta
                    </label>
                    <input type="text" id="requested_service_or_department" name="requested_service_or_department"
                           value="{{ old('requested_service_or_department') }}"
                           placeholder="Contoh: Poli Bedah, IGD, Rawat Inap, dll."
                           class="ui-form-control w-full rounded-lg border shadow-sm focus:ring-[var(--focus-ring)] focus:border-[var(--primary)]">
                </div>

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium ui-text-secondary mb-1">
                        Alasan Rujukan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" name="reason" rows="3" required
                              placeholder="Jelaskan alasan klinis utama diperlukannya rujukan..."
                              class="ui-form-control w-full rounded-lg border shadow-sm focus:ring-[var(--focus-ring)] focus:border-[var(--primary)]">{{ old('reason') }}</textarea>
                </div>

                <!-- Clinical Summary (minimum necessary) -->
                <div>
                    <label for="clinical_summary" class="block text-sm font-medium ui-text-secondary mb-1">
                        Ringkasan Klinis untuk Faskes Tujuan <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs ui-text-muted mb-2">
                        Cantumkan informasi minimum yang diperlukan oleh faskes tujuan. Hindari menyertakan data pasien lain atau informasi tidak relevan.
                    </p>
                    <textarea id="clinical_summary" name="clinical_summary" rows="5" required
                              placeholder="Keluhan utama, kronologi singkat, kondisi saat ini, pemeriksaan penunjang yang relevan..."
                              class="ui-form-control w-full rounded-lg border shadow-sm focus:ring-[var(--focus-ring)] focus:border-[var(--primary)]">{{ old('clinical_summary') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('visits.show', $visit->id) }}"
                   class="px-4 py-2 text-sm font-medium ui-text-secondary bg-[var(--surface)] border border-[var(--border)] rounded-lg hover:bg-[var(--surface-subtle)] transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-semibold text-[var(--action-text)] bg-[var(--action-bg)] hover:bg-[var(--action-hover)] rounded-lg transition-colors shadow-sm">
                    Buat Rujukan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
