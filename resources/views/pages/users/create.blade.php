<x-app-layout>
    <x-slot name="title">Tambah User Lokal</x-slot>

    <div class="space-y-6 max-w-3xl">
        <!-- Header -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('users.index') }}" class="text-[var(--foreground-muted)] hover:text-[var(--foreground)] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Tambah User Lokal Baru</h1>
            </div>
            <p class="text-sm text-[var(--foreground-muted)] ml-7">
                Buat akun pengguna yang dikelola langsung di aplikasi ini — tidak terhubung ke Gate SSO.
            </p>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-sm space-y-1">
                <p class="font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Harap perbaiki kesalahan berikut:
                </p>
                <ul class="list-disc list-inside space-y-0.5 ml-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}" id="create-user-form">
            @csrf

            <!-- STEP 1: Mode Person -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-5">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Langkah 1 — Data Person</h2>

                <!-- Person Mode Toggle -->
                <div class="grid grid-cols-2 gap-3">
                    <label id="mode-new-label"
                        class="relative flex flex-col gap-1.5 p-4 rounded-xl border-2 cursor-pointer transition-all
                               {{ old('person_mode', 'new') === 'new' ? 'border-[var(--primary)] bg-[var(--primary)]/5' : 'border-[var(--border)] hover:border-[var(--primary)]/50' }}">
                        <input type="radio" name="person_mode" value="new" id="mode-new"
                               class="sr-only"
                               {{ old('person_mode', 'new') === 'new' ? 'checked' : '' }}
                               onchange="togglePersonMode(this)">
                        <span class="font-bold text-sm text-[var(--foreground)]">Buat Person Baru</span>
                        <span class="text-xs text-[var(--foreground-muted)]">Tambah person baru sekaligus dengan akun ini</span>
                    </label>
                    <label id="mode-existing-label"
                        class="relative flex flex-col gap-1.5 p-4 rounded-xl border-2 cursor-pointer transition-all
                               {{ old('person_mode') === 'existing' ? 'border-[var(--primary)] bg-[var(--primary)]/5' : 'border-[var(--border)] hover:border-[var(--primary)]/50' }}">
                        <input type="radio" name="person_mode" value="existing" id="mode-existing"
                               class="sr-only"
                               {{ old('person_mode') === 'existing' ? 'checked' : '' }}
                               onchange="togglePersonMode(this)">
                        <span class="font-bold text-sm text-[var(--foreground)]">Pilih Person yang Ada</span>
                        <span class="text-xs text-[var(--foreground-muted)]">Person sudah di DB tapi belum punya akun</span>
                    </label>
                </div>

                <!-- Person Baru Fields -->
                <div id="fields-new-person" class="{{ old('person_mode') === 'existing' ? 'hidden' : '' }} space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="user_type" class="text-xs font-semibold text-[var(--foreground-muted)]">Tipe Pengguna <span class="text-rose-500">*</span></label>
                            <select name="user_type" id="user_type"
                                    class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors">
                                @foreach(['santri' => 'Santri', 'guru' => 'Guru / Ustadz', 'pengasuh' => 'Pengasuh', 'staff' => 'Staff', 'admin' => 'Admin', 'wali' => 'Wali'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('user_type', 'staff') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label for="nis_nip" class="text-xs font-semibold text-[var(--foreground-muted)]">NIS / NIP</label>
                            <input type="text" name="nis_nip" id="nis_nip"
                                   value="{{ old('nis_nip') }}"
                                   placeholder="Nomor identitas (opsional)"
                                   class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors font-mono">
                        </div>
                    </div>
                </div>

                <!-- Person Existing Fields -->
                <div id="fields-existing-person" class="{{ old('person_mode') !== 'existing' ? 'hidden' : '' }} space-y-1">
                    <label for="person_id" class="text-xs font-semibold text-[var(--foreground-muted)]">Pilih Person <span class="text-rose-500">*</span></label>
                    @if($availablePersons->isEmpty())
                        <p class="text-xs text-amber-600 dark:text-amber-400 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900">
                            Semua person di database sudah memiliki akun pengguna.
                        </p>
                    @else
                        <select name="person_id" id="person_id"
                                class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors">
                            <option value="">-- Pilih Person --</option>
                            @foreach($availablePersons as $p)
                                <option value="{{ $p->id }}" {{ old('person_id') === $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                    ({{ ucfirst($p->user_type ?? 'unknown') }}{{ $p->nis_nip ? ' · ' . $p->nis_nip : '' }}{{ $p->gate_user_id ? ' · Gate SSO' : '' }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <!-- STEP 2: Akun -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4 mt-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Langkah 2 — Akun Login</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="name" class="text-xs font-semibold text-[var(--foreground-muted)]">Nama Tampilan <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="name"
                               value="{{ old('name') }}"
                               required autocomplete="off"
                               placeholder="Nama lengkap pengguna"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors">
                    </div>
                    <div class="space-y-1">
                        <label for="email" class="text-xs font-semibold text-[var(--foreground-muted)]">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email') }}"
                               required autocomplete="off"
                               placeholder="email@contoh.com"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="password" class="text-xs font-semibold text-[var(--foreground-muted)]">Kata Sandi <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" id="password"
                               required autocomplete="new-password"
                               placeholder="Min. 8 karakter, huruf & angka"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors">
                    </div>
                    <div class="space-y-1">
                        <label for="password_confirmation" class="text-xs font-semibold text-[var(--foreground-muted)]">Konfirmasi Kata Sandi <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               required autocomplete="new-password"
                               placeholder="Ulangi kata sandi"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors">
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-[var(--border)] text-[var(--primary)] focus:ring-[var(--primary)]">
                    <label for="is_active" class="text-sm text-[var(--foreground)] cursor-pointer">
                        Aktifkan akun ini segera setelah dibuat
                    </label>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Kata sandi <strong>tidak dikirim via email</strong>. Sampaikan langsung kepada pengguna secara aman setelah akun dibuat.</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 mt-4">
                <a href="{{ route('users.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] border border-[var(--border)] transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors shadow-xs">
                    Buat Akun Pengguna
                </button>
            </div>
        </form>
    </div>

    <script>
        function togglePersonMode(radio) {
            const isNew = radio.value === 'new';

            document.getElementById('fields-new-person').classList.toggle('hidden', !isNew);
            document.getElementById('fields-existing-person').classList.toggle('hidden', isNew);

            // Update label styles
            const newLabel = document.getElementById('mode-new-label');
            const existingLabel = document.getElementById('mode-existing-label');
            const activeClass = ['border-[var(--primary)]', 'bg-[var(--primary)]/5'];
            const inactiveClass = ['border-[var(--border)]'];

            if (isNew) {
                newLabel.classList.add(...activeClass);
                newLabel.classList.remove(...inactiveClass);
                existingLabel.classList.remove(...activeClass);
                existingLabel.classList.add(...inactiveClass);
            } else {
                existingLabel.classList.add(...activeClass);
                existingLabel.classList.remove(...inactiveClass);
                newLabel.classList.remove(...activeClass);
                newLabel.classList.add(...inactiveClass);
            }
        }

        // Init on load
        document.querySelectorAll('input[name="person_mode"]').forEach(r => {
            if (r.checked) togglePersonMode(r);
        });
    </script>
</x-app-layout>
