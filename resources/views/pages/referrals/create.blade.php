<x-app-layout>
    <x-slot name="title">Buat Rujukan — {{ $visit->patient->person->name }} — SABIRA POSKESTREN</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <a href="{{ route('visits.show', $visit->id) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Kembali ke Kunjungan</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Pembuatan Rujukan Eksternal</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kunjungan: <span class="font-mono font-semibold">{{ $visit->visit_number }}</span> |
                Pasien: <span class="font-semibold">{{ $visit->patient->person->name }}</span>
            </p>
        </div>

        @if($visit->latestAssessment?->status !== 'finalized')
            <div class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Pengkajian Klinis Belum Difinalisasi</h3>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-400">Rujukan memerlukan pengkajian klinis yang telah difinalisasi oleh petugas berwenang.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Emergency Warning Banner -->
        <div class="rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300">⚠ Rujukan Darurat Tidak Perlu Menunggu Konsultasi</h3>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
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
                <div class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-4">
                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 space-y-5 border border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3">Informasi Rujukan</h2>

                <!-- Urgency -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tingkat Urgensi <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-3">
                        @foreach(['routine' => ['label' => 'Rutin', 'color' => 'green'], 'urgent' => ['label' => 'Urgent', 'color' => 'orange'], 'emergency' => ['label' => 'Darurat / Emergency', 'color' => 'red']] as $val => $opt)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="urgency" value="{{ $val }}" {{ old('urgency') === $val ? 'checked' : ($val === 'routine' && !old('urgency') ? 'checked' : '') }} class="sr-only peer">
                                <div class="peer-checked:ring-2 peer-checked:ring-{{ $opt['color'] }}-500 peer-checked:bg-{{ $opt['color'] }}-50 dark:peer-checked:bg-{{ $opt['color'] }}-900/30 border border-gray-200 dark:border-gray-600 rounded-lg p-3 text-center transition-all hover:border-{{ $opt['color'] }}-300">
                                    <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $opt['label'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Destination Partner -->
                <div>
                    <label for="healthcare_partner_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Fasilitas Kesehatan Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select id="healthcare_partner_id" name="healthcare_partner_id" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
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
                    <label for="requested_service_or_department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Layanan / Poli yang Diminta
                    </label>
                    <input type="text" id="requested_service_or_department" name="requested_service_or_department"
                           value="{{ old('requested_service_or_department') }}"
                           placeholder="Contoh: Poli Bedah, IGD, Rawat Inap, dll."
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Alasan Rujukan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" name="reason" rows="3" required
                              placeholder="Jelaskan alasan klinis utama diperlukannya rujukan..."
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('reason') }}</textarea>
                </div>

                <!-- Clinical Summary (minimum necessary) -->
                <div>
                    <label for="clinical_summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Ringkasan Klinis untuk Faskes Tujuan <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        Cantumkan informasi minimum yang diperlukan oleh faskes tujuan. Hindari menyertakan data pasien lain atau informasi tidak relevan.
                    </p>
                    <textarea id="clinical_summary" name="clinical_summary" rows="5" required
                              placeholder="Keluhan utama, kronologi singkat, kondisi saat ini, pemeriksaan penunjang yang relevan..."
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('clinical_summary') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('visits.show', $visit->id) }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                    Buat Rujukan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
