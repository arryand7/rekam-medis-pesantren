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

        @php
            $initialPatient = $selectedPatient ? [
                'id' => $selectedPatient->id,
                'label' => $selectedPatient->person->name.' ('.$selectedPatient->patient_number.') - '.ucfirst(str_replace('_', ' ', $selectedPatient->person->user_type)),
            ] : null;
        @endphp

        <form action="{{ route('visits.store') }}" method="POST" class="ui-card p-6 rounded-2xl shadow-xs space-y-6"
              x-data="patientLookup({ searchUrl: @js(route('visits.patient-search')), initial: @js($initialPatient) })"
              @submit="if (!selectedId) { $event.preventDefault(); error = 'Pilih pasien dari hasil pencarian.'; open = true; $refs.searchInput.focus(); }">
            @csrf

            <!-- Patient Selector -->
            <div class="space-y-2" @click.outside="open = false">
                <label for="patient_search" class="ui-form-label block text-xs uppercase tracking-wider">Cari dan Pilih Pasien <span class="text-rose-600 dark:text-rose-400">*</span></label>
                <input type="hidden" name="patient_id" x-model="selectedId">

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[var(--foreground-muted)]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="search" id="patient_search" x-ref="searchInput" x-model="query" required
                           @input.debounce.300ms="search()" @focus="if (results.length > 0 && !selectedId) open = true"
                           @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)"
                           @keydown.enter.prevent="chooseActive()" @keydown.escape="open = false"
                           role="combobox" aria-autocomplete="list" aria-controls="patient-search-results"
                           :aria-expanded="open.toString()" :aria-activedescendant="activeIndex >= 0 ? 'patient-result-' + activeIndex : null"
                           autocomplete="off" spellcheck="false" placeholder="Ketik nama, nomor RM, atau NIS/NIP..."
                           class="ui-form-control w-full rounded-xl border py-3 pl-11 pr-20 text-sm focus:ring-2 focus:ring-[var(--focus-ring)]">

                    <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-2">
                        <span x-show="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-sky-600 border-t-transparent" aria-hidden="true"></span>
                        <button x-show="query !== ''" type="button" @click="clear()"
                                class="rounded-lg p-1.5 text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--focus-ring)]"
                                aria-label="Hapus pilihan dan pencarian pasien">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div x-show="open" x-cloak id="patient-search-results" role="listbox"
                         class="absolute z-50 mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-[var(--border)] bg-[var(--surface)] p-1.5 shadow-xl">
                        <template x-for="(patient, index) in results" :key="patient.id">
                            <button type="button" role="option" :id="'patient-result-' + index"
                                    :aria-selected="(activeIndex === index).toString()"
                                    @mouseenter="activeIndex = index" @click="select(patient)"
                                    :class="activeIndex === index ? 'bg-[var(--primary)] text-white' : 'text-[var(--foreground)] hover:bg-[var(--surface-muted)]'"
                                    class="block w-full rounded-lg px-3 py-2.5 text-left transition-colors">
                                <span class="block text-sm font-semibold" x-text="patient.name"></span>
                                <span class="mt-0.5 block text-xs opacity-80" x-text="patient.patient_number + (patient.nis_nip ? ' · ' + patient.nis_nip : '') + ' · ' + patient.user_type.replaceAll('_', ' ')"></span>
                            </button>
                        </template>

                        <p x-show="!loading && results.length === 0 && query.trim().length >= 2 && !error" class="px-3 py-4 text-center text-sm text-[var(--foreground-muted)]">Pasien tidak ditemukan.</p>
                        <p x-show="query.trim().length < 2 && !selectedId" class="px-3 py-4 text-center text-sm text-[var(--foreground-muted)]">Ketik minimal 2 karakter untuk mencari.</p>
                    </div>
                </div>

                <div x-show="selectedId" x-cloak class="flex items-start justify-between gap-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2.5 text-sm text-sky-900 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-100">
                    <div>
                        <span class="block text-[11px] font-semibold uppercase tracking-wide">Pasien terpilih</span>
                        <span class="font-semibold" x-text="selectedLabel"></span>
                    </div>
                    <button type="button" @click="clear()" class="shrink-0 rounded-lg px-2 py-1 text-xs font-semibold hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:hover:bg-sky-900">Ganti</button>
                </div>

                <p x-show="error" x-text="error" role="alert" class="text-xs font-semibold text-rose-700 dark:text-rose-300"></p>
                @error('patient_id')
                    <p role="alert" class="text-xs font-semibold text-rose-700 dark:text-rose-300">{{ $message }}</p>
                @enderror
                <p class="ui-form-hint text-xs">Cari berdasarkan nama, nomor rekam medis, atau NIS/NIP, lalu pilih hasil yang sesuai. Data identitas mengikuti sumber Gate.</p>
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

    <script>
        function patientLookup(config) {
            return {
                searchUrl: config.searchUrl,
                query: config.initial?.label ?? '',
                selectedId: config.initial?.id ?? '',
                selectedLabel: config.initial?.label ?? '',
                results: [],
                loading: false,
                open: false,
                activeIndex: -1,
                error: '',
                requestController: null,

                async search() {
                    if (this.query !== this.selectedLabel) {
                        this.selectedId = '';
                        this.selectedLabel = '';
                    }

                    const term = this.query.trim();
                    this.error = '';
                    this.activeIndex = -1;

                    if (term.length < 2) {
                        this.results = [];
                        this.open = term.length > 0;
                        this.loading = false;
                        this.requestController?.abort();
                        return;
                    }

                    this.requestController?.abort();
                    const controller = new AbortController();
                    this.requestController = controller;
                    this.loading = true;
                    this.open = true;

                    try {
                        const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(term)}`, {
                            headers: { 'Accept': 'application/json' },
                            signal: controller.signal,
                        });

                        if (!response.ok) throw new Error('Pencarian pasien gagal.');

                        const payload = await response.json();
                        this.results = payload.data ?? [];
                        this.activeIndex = this.results.length > 0 ? 0 : -1;
                    } catch (exception) {
                        if (exception.name !== 'AbortError') {
                            this.results = [];
                            this.error = 'Pencarian pasien belum dapat dimuat. Silakan coba lagi.';
                        }
                    } finally {
                        if (this.requestController === controller) {
                            this.loading = false;
                        }
                    }
                },

                select(patient) {
                    this.selectedId = patient.id;
                    this.selectedLabel = patient.label;
                    this.query = patient.label;
                    this.results = [];
                    this.open = false;
                    this.activeIndex = -1;
                    this.error = '';
                },

                clear() {
                    this.requestController?.abort();
                    this.query = '';
                    this.selectedId = '';
                    this.selectedLabel = '';
                    this.results = [];
                    this.open = false;
                    this.activeIndex = -1;
                    this.error = '';
                    this.$nextTick(() => this.$refs.searchInput.focus());
                },

                move(direction) {
                    if (!this.open || this.results.length === 0) return;
                    this.activeIndex = (this.activeIndex + direction + this.results.length) % this.results.length;
                    this.$nextTick(() => document.getElementById(`patient-result-${this.activeIndex}`)?.scrollIntoView({ block: 'nearest' }));
                },

                chooseActive() {
                    if (this.open && this.activeIndex >= 0) {
                        this.select(this.results[this.activeIndex]);
                    }
                },
            };
        }
    </script>
</x-app-layout>
