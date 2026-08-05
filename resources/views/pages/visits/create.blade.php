<x-app-layout>
    <x-slot name="title">Registrasi Kunjungan Medis — SABIRA POSKESTREN</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Registrasi Intake Kunjungan Medis</h1>
            <p class="text-sm text-[var(--foreground-muted)] mt-1">Catat pendaftaran kedatangan pasien di Poskestren. Waktu kedatangan dicatat otomatis oleh server.</p>
        </div>

        @if(session('error'))
            <div class="bg-rose-500/10 border-l-4 border-rose-500 p-4 rounded-xl text-xs text-rose-700 dark:text-rose-300 font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('visits.store') }}" method="POST" class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-6">
            @csrf

            <!-- Patient Selector -->
            <div class="space-y-2">
                <label for="patient_id" class="block text-xs font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Pilih Pasien <span class="text-rose-500">*</span></label>
                <select name="patient_id" id="patient_id" required class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-sm text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">
                    <option value="">-- Pilih Pasien Terdaftar --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (request('patient_id') == $p->id || old('patient_id') == $p->id) ? 'selected' : '' }}>
                            {{ $p->person->name }} ({{ $p->patient_number }}) - {{ ucfirst($p->person->user_type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Chief Complaint -->
            <div class="space-y-2">
                <label for="chief_complaint" class="block text-xs font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Keluhan Utama <span class="text-rose-500">*</span></label>
                <textarea name="chief_complaint" id="chief_complaint" rows="3" required placeholder="Jelaskan alasan kedatangan atau keluhan utama santri/pasien..." class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-sm text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">{{ old('chief_complaint') }}</textarea>
            </div>

            <!-- Grid 2 Cols: Reporting Type & Origin Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="reporting_type" class="block text-xs font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Pengantar / Pelapor</label>
                    <select name="reporting_type" id="reporting_type" class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-sm text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">
                        <option value="self">Datang Sendiri</option>
                        <option value="dormitory_guardian">Wali Asrama / Pengasuh</option>
                        <option value="teacher">Guru / Ustadz</option>
                        <option value="friend">Teman / Santri Lain</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="origin_location" class="block text-xs font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Lokasi Asal (Kamar/Gedung)</label>
                    <input type="text" name="origin_location" id="origin_location" value="{{ old('origin_location') }}" placeholder="Contoh: Asrama Al-Ghazali Lt 2" class="w-full px-4 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-sm text-[var(--foreground)] focus:ring-2 focus:ring-[var(--focus-ring)]">
                </div>
            </div>

            <!-- Active Visit Override Checkbox -->
            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 space-y-3">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="override_active" id="override_active" value="1" class="rounded border-amber-500 text-[var(--primary)] focus:ring-[var(--focus-ring)]">
                    <label for="override_active" class="text-xs font-bold text-amber-900 dark:text-amber-100">
                        Override Kunjungan Aktif (Bila pasien memiliki kunjungan gantung)
                    </label>
                </div>
                <div class="space-y-1">
                    <label for="override_reason" class="block text-[11px] font-semibold text-amber-800 dark:text-amber-200">Alasan Override (Wajib diisi bila mencentang override):</label>
                    <input type="text" name="override_reason" id="override_reason" placeholder="Contoh: Kunjungan sebelumnya belum ditutup tetapi ada kondisi darurat baru" class="w-full px-3 py-2 rounded-lg border border-amber-300 dark:border-amber-800 bg-[var(--surface)] text-xs text-[var(--foreground)]">
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
