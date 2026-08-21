<x-app-layout>
    <x-slot name="title">Edit Akun: {{ $user->name }}</x-slot>

    <div class="space-y-6 max-w-3xl">
        <!-- Header -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('users.show', $user->id) }}" class="text-[var(--foreground-muted)] hover:text-[var(--foreground)] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Edit Akun: {{ $user->name }}</h1>
                    <p class="text-xs text-[var(--foreground-muted)] font-mono mt-0.5">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        <!-- Gate SSO Warning Banner -->
        @if ($isGateManaged)
            <div class="p-4 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 flex items-start gap-3">
                <svg class="w-5 h-5 text-sky-600 dark:text-sky-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <div>
                    <p class="text-sm font-bold text-sky-800 dark:text-sky-300">Identitas Dikelola Gate SSO</p>
                    <p class="text-xs text-sky-700 dark:text-sky-400 mt-1">
                        Nama dan email akun ini disinkronisasi dari Gate SSO dan tidak bisa diubah di sini.
                        Anda hanya dapat mengubah <strong>kata sandi lokal</strong> dan <strong>status aktif</strong>.
                    </p>
                </div>
            </div>
        @endif

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

        <form method="POST" action="{{ route('users.update', $user->id) }}" id="edit-user-form">
            @csrf
            @method('PUT')

            <!-- Identity Fields -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Identitas Akun</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="name" class="text-xs font-semibold text-[var(--foreground-muted)]">
                            Nama Tampilan @if(!$isGateManaged)<span class="text-rose-500">*</span>@endif
                        </label>
                        <input type="text" name="name" id="name"
                               value="{{ old('name', $user->name) }}"
                               {{ $isGateManaged ? 'readonly' : 'required' }}
                               placeholder="Nama lengkap pengguna"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors
                                      {{ $isGateManaged ? 'opacity-60 cursor-not-allowed' : '' }}">
                    </div>
                    <div class="space-y-1">
                        <label for="email" class="text-xs font-semibold text-[var(--foreground-muted)]">
                            Email @if(!$isGateManaged)<span class="text-rose-500">*</span>@endif
                        </label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', $user->email) }}"
                               {{ $isGateManaged ? 'readonly' : 'required' }}
                               placeholder="email@contoh.com"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors font-mono
                                      {{ $isGateManaged ? 'opacity-60 cursor-not-allowed' : '' }}">
                    </div>
                </div>

                @if($user->person)
                    <div class="p-3.5 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] text-xs text-[var(--foreground-muted)] flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-[var(--primary)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Terhubung ke person: <strong class="text-[var(--foreground)]">{{ $user->person->name }}</strong>
                        ({{ ucfirst($user->person->user_type ?? '-') }}{{ $user->person->nis_nip ? ' · ' . $user->person->nis_nip : '' }})
                        @if($isGateManaged)
                            <span class="ml-auto px-2 py-0.5 rounded bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 font-semibold">Gate SSO</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Password -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4 mt-4">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)]">Kata Sandi</h2>
                    <p class="text-xs text-[var(--foreground-muted)] mt-1">Kosongkan jika tidak ingin mengubah kata sandi.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="password" class="text-xs font-semibold text-[var(--foreground-muted)]">Kata Sandi Baru</label>
                        <input type="password" name="password" id="password"
                               autocomplete="new-password"
                               placeholder="Kosongkan untuk tidak mengubah"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors">
                    </div>
                    <div class="space-y-1">
                        <label for="password_confirmation" class="text-xs font-semibold text-[var(--foreground-muted)]">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               autocomplete="new-password"
                               placeholder="Ulangi kata sandi baru"
                               class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] placeholder-[var(--foreground-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition-colors">
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs mt-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[var(--foreground-muted)] mb-4">Status Akun</h2>
                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)]">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ $user->is_active ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-[var(--border)] text-[var(--primary)] focus:ring-[var(--primary)]">
                    <label for="is_active" class="text-sm text-[var(--foreground)] cursor-pointer">
                        Akun ini <strong>aktif</strong> dan dapat login ke sistem
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 mt-4">
                <a href="{{ route('users.show', $user->id) }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold text-[var(--foreground-muted)] hover:bg-[var(--surface-muted)] border border-[var(--border)] transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-sm font-bold bg-[var(--primary)] text-white hover:bg-[var(--primary-hover)] transition-colors shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
