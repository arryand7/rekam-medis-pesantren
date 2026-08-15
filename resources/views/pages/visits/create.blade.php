<x-app-layout>
    <x-slot name="title">Registrasi Kunjungan Medis</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="ui-card p-6 rounded-2xl shadow-xs">
            <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Registrasi Intake Kunjungan Medis</h1>
            <p class="text-sm ui-text-muted mt-1">Catat pendaftaran kedatangan pasien di Poskestren. Waktu kedatangan dicatat otomatis oleh server.</p>
        </div>

        @if(session('error'))
            <div class="ui-banner-danger border-l-4 p-4 rounded-xl text-xs font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('visits.store') }}" method="POST" class="ui-card p-6 rounded-2xl shadow-xs space-y-6">
            @csrf

            <!-- Patient Selector -->
            <div class="space-y-2">
                <label for="patient_id" class="ui-form-label block text-xs uppercase tracking-wider">Pilih Pasien <span class="text-rose-600 dark:text-rose-400">*</span></label>
                <select name="patient_id" id="patient_id" required class="ui-form-control w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-[var(--focus-ring)]">
                    <option value="">-- Pilih Pasien Terdaftar --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (request('patient_id') == $p->id || old('patient_id') == $p->id) ? 'selected' : '' }}>
                            {{ $p->person->name }} ({{ $p->patient_number }}) - {{ ucfirst($p->person->user_type) }}
                        </option>
                    @endforeach
                </select>
                <p class="ui-form-hint text-xs">Pilih identitas pasien yang sudah terdaftar; data identitas mengikuti sumber Gate.</p>
            </div>

            <!-- Chief Complaint -->
            <div class="space-y-2">
                <label for="chief_complaint" class="ui-form-label block text-xs uppercase tracking-wider">Keluhan Utama <span class="text-rose-600 dark:text-rose-400">*</span></label>
                <textarea name="chief_complaint" id="chief_complaint" rows="3" required placeholder="Jelaskan alasan kedatangan atau keluhan utama santri/pasien..." class="ui-form-control w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-[var(--focus-ring)]">{{ old('chief_complaint') }}</textarea>
                <p class="ui-form-hint text-xs">Tuliskan keluhan sebagaimana disampaikan saat intake; assessment klinis dicatat pada tahap berikutnya.</p>
            </div>

            <!-- Grid 2 Cols: Reporting Type & Origin Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="reporting_type" class="ui-form-label block text-xs uppercase tracking-wider">Pengantar / Pelapor</label>
                    <select name="reporting_type" id="reporting_type" class="ui-form-control w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-[var(--focus-ring)]">
                        <option value="self">Datang Sendiri</option>
                        <option value="dormitory_guardian">Wali Asrama / Pengasuh</option>
                        <option value="teacher">Guru / Ustadz</option>
                        <option value="friend">Teman / Santri Lain</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="origin_location" class="ui-form-label block text-xs uppercase tracking-wider">Lokasi Asal (Kamar/Gedung)</label>
                    <input type="text" name="origin_location" id="origin_location" value="{{ old('origin_location') }}" placeholder="Contoh: Asrama Al-Ghazali Lt 2" class="ui-form-control w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-[var(--focus-ring)]">
                </div>
            </div>

            <!-- Active Visit Override Checkbox -->
            <div class="ui-banner-warning p-4 rounded-xl space-y-3">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="override_active" id="override_active" value="1" class="rounded border-amber-500 text-[var(--primary)] focus:ring-[var(--focus-ring)]">
                    <label for="override_active" class="text-xs font-bold">
                        Override Kunjungan Aktif (Bila pasien memiliki kunjungan gantung)
                    </label>
                </div>
                <p class="text-xs leading-relaxed">Gunakan hanya bila kunjungan sebelumnya belum ditutup dan intake baru tetap harus dicatat. Alasan override akan diaudit.</p>
                <div class="space-y-1">
                    <label for="override_reason" class="block text-[11px] font-semibold">Alasan Override (Wajib diisi bila mencentang override):</label>
                    <input type="text" name="override_reason" id="override_reason" placeholder="Contoh: Kunjungan sebelumnya belum ditutup tetapi ada kondisi darurat baru" class="ui-form-control w-full px-3 py-2 rounded-lg border text-xs">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-[var(--border)]">
                <a href="{{ route('visits.index') }}" class="px-4 py-2 rounded-xl text-xs font-medium text-[var(--foreground-muted)] hover:text-[var(--foreground)]">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors shadow-xs">
                    Simpan Registrasi Intake
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
