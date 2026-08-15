<x-app-layout>
    <x-slot name="title">Akses Pengguna: {{ $user->name }}</x-slot>

    @php
        $userRoleNames = $user->roles->pluck('name')->toArray();
        $userDirectPermNames = $user->permissions->pluck('name')->toArray();
        $isSelf = auth()->id() === $user->id;
    @endphp

    <div class="space-y-6" x-data="{
        effectiveSearch: '',
        directSearch: '',
        matchesEffective(name, displayName, source) {
            if (!this.effectiveSearch) return true;
            const q = this.effectiveSearch.toLowerCase();
            return name.toLowerCase().includes(q) || displayName.toLowerCase().includes(q) || source.toLowerCase().includes(q);
        },
        matchesDirect(name, displayName) {
            if (!this.directSearch) return true;
            const q = this.directSearch.toLowerCase();
            return name.toLowerCase().includes(q) || displayName.toLowerCase().includes(q);
        }
    }">
        <!-- Top Header & Breadcrumb -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <a href="{{ route('users.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Daftar Pengguna
                </a>
                <div class="flex items-center gap-3 mt-1 flex-wrap">
                    <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">
                        {{ $user->name }}
                    </h1>
                    @if($user->isSuperAdmin())
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-md bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            SUPER ADMIN
                        </span>
                    @endif
                    @if($user->is_active)
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Non-Aktif
                        </span>
                    @endif
                </div>
                <div class="text-xs text-[var(--foreground-muted)] font-mono mt-0.5">
                    {{ $user->email }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Status Toggle Button -->
                <form method="POST" action="{{ route('users.toggle-status', $user->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktifitas akun pengguna ini?');">
                    @csrf
                    @if($user->is_active)
                        <button type="submit" class="px-3.5 py-2 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-xs font-semibold transition-colors">
                            Nonaktifkan Akun
                        </button>
                    @else
                        <button type="submit" class="px-3.5 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-xs font-semibold transition-colors">
                            Aktifkan Akun
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <!-- Feedback Messages -->
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs space-y-1">
                <div class="font-bold">Gagal memperbarui data:</div>
                <ul class="list-disc list-inside pl-2 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Identity & Metadata Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase text-[var(--foreground-muted)] block">Tautan Person (Gate SSO)</span>
                <div class="text-sm font-bold text-[var(--foreground)] mt-1">
                    {{ $user->person?->name ?? 'Akun Teknis Murni (Tanpa Person)' }}
                </div>
                @if($user->person)
                    <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">
                        Tipe: <span class="capitalize font-semibold">{{ $user->person->user_type }}</span> &bull; NIS/NIP: {{ $user->person->nis_nip ?? '-' }}
                    </div>
                @endif
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase text-[var(--foreground-muted)] block">Total Effective Permissions</span>
                <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1">
                    {{ count($effectivePermissions) }} Hak Akses
                </div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">
                    Dari {{ $user->roles->count() }} Role + {{ $user->permissions->count() }} Direct Exception
                </div>
            </div>

            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase text-[var(--foreground-muted)] block">Login Terakhir</span>
                <div class="text-sm font-bold text-[var(--foreground)] mt-1">
                    {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Belum Pernah' }}
                </div>
                <div class="text-[11px] text-[var(--foreground-muted)] mt-0.5">
                    Tema: <span class="capitalize font-semibold">{{ $user->theme_preference }}</span>
                </div>
            </div>
        </div>

        <!-- Role Assignment Card -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--border)] pb-3">
                <div>
                    <h2 class="text-base font-bold text-[var(--foreground)]">1. Penugasan Role Aplikatif (Assigned Roles)</h2>
                    <p class="text-xs text-[var(--foreground-muted)] mt-0.5">Pilih role yang ditugaskan kepada pengguna ini. Setiap role mewarisi paket hak akses terkait.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('users.roles.update', $user->id) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($allRoles as $r)
                        @php
                            $isAssigned = in_array($r->name, $userRoleNames, true);
                            $isSuperAdminRole = $r->name === 'super_admin';
                            $isDisabled = $isSuperAdminRole && ! $isActorSuperAdmin;
                        @endphp
                        <label class="p-3.5 rounded-xl border transition-colors flex items-start gap-3 cursor-pointer {{ $isDisabled ? 'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-900 border-gray-200 dark:border-gray-800' : 'bg-[var(--surface-muted)] border-[var(--border)] hover:border-blue-400' }}">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $r->id }}"
                                {{ $isAssigned ? 'checked' : '' }}
                                {{ $isDisabled ? 'disabled' : '' }}
                                class="mt-1 rounded text-blue-600 focus:ring-blue-500 border-[var(--border)]">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-bold text-[var(--foreground)]">
                                        {{ $r->display_name ?? $r->name }}
                                    </span>
                                    @if($r->isProtected())
                                        <span class="px-1.5 py-0.2 text-[9px] font-bold rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900">
                                            PROTECTED
                                        </span>
                                    @endif
                                </div>
                                <div class="font-mono text-[10px] text-[var(--foreground-muted)]">
                                    {{ $r->name }}
                                </div>
                                <p class="text-[11px] text-[var(--foreground-muted)] line-clamp-2 mt-1">
                                    {{ $r->description ?? 'Tidak ada deskripsi.' }}
                                </p>
                                @if($isDisabled)
                                    <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-1">
                                        * Hanya Super Admin yang dapat menugaskan role ini.
                                    </p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                        Simpan Penugasan Role
                    </button>
                </div>
            </form>
        </div>

        <!-- Direct Permissions (Exceptions) Card -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[var(--border)] pb-3">
                <div>
                    <h2 class="text-base font-bold text-[var(--foreground)]">2. Hak Akses Langsung / Pengecualian (Direct Permissions)</h2>
                    <p class="text-xs text-[var(--foreground-muted)] mt-0.5">Penugasan permission di luar paket role standar pengguna.</p>
                </div>
                <div class="w-full sm:w-64">
                    <input
                        type="text"
                        x-model="directSearch"
                        placeholder="Saring daftar exception..."
                        class="w-full text-xs p-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Caution Banner -->
            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <span class="font-bold">Pedoman Tata Kelola Keamanan:</span>
                    Utamakan manajemen hak akses berbasis Role. Penugasan hak akses langsung (Direct Permissions) hanya digunakan untuk kebutuhan pengecualian operasional sementara atau audit darurat.
                </div>
            </div>

            @if($isSelf && ! $isActorSuperAdmin)
                <div class="p-3.5 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-xs">
                    * Demi mencegah <em>Privilege Escalation</em>, administrator tidak dapat mengubah hak akses langsung pada akun sendiri.
                </div>
            @else
                <form method="POST" action="{{ route('users.permissions.update', $user->id) }}" class="space-y-4">
                    @csrf

                    <div class="space-y-4 max-h-[400px] overflow-y-auto pr-1">
                        @foreach($groupedPermissions as $groupKey => $group)
                            <div class="p-4 rounded-xl bg-[var(--surface-muted)]/60 border border-[var(--border)] space-y-2">
                                <h3 class="text-xs font-bold text-[var(--foreground)] flex items-center gap-1.5">
                                    {{ $group['title'] }}
                                    <span class="text-[10px] text-[var(--foreground-muted)]">({{ $group['permissions']->count() }})</span>
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @foreach($group['permissions'] as $p)
                                        @php
                                            $isDirect = in_array($p->name, $userDirectPermNames, true);
                                            $isProtected = $p->isProtected();
                                            $isDisabled = $isProtected && ! $isActorSuperAdmin;
                                        @endphp
                                        <label
                                            x-show="matchesDirect('{{ $p->name }}', '{{ addslashes($p->display_name) }}')"
                                            class="flex items-start gap-2 p-2 rounded-lg border text-xs cursor-pointer {{ $isDisabled ? 'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-900 border-gray-200 dark:border-gray-800' : 'bg-[var(--surface)] border-[var(--border)] hover:border-blue-400' }}">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $p->name }}"
                                                {{ $isDirect ? 'checked' : '' }}
                                                {{ $isDisabled ? 'disabled' : '' }}
                                                class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 border-[var(--border)]">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold text-[var(--foreground)] truncate">{{ $p->display_name }}</div>
                                                <div class="font-mono text-[10px] text-[var(--foreground-muted)] truncate">{{ $p->name }}</div>
                                                @if($isProtected)
                                                    <span class="px-1 py-0.2 text-[8px] font-bold rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300">PROTECTED</span>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                            Simpan Hak Akses Langsung (Exception)
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <!-- Effective Permissions Preview Card -->
        <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[var(--border)] pb-3">
                <div>
                    <h2 class="text-base font-bold text-[var(--foreground)] flex items-center gap-2">
                        3. Pratinjau Hak Akses Efektif (Effective Permissions Preview)
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">
                            {{ count($effectivePermissions) }} Hak Akses Aktif
                        </span>
                    </h2>
                    <p class="text-xs text-[var(--foreground-muted)] mt-0.5">
                        Menampilkan seluruh hak akses aktif pengguna beserta asal sumber penugasannya (Role vs Direct).
                    </p>
                </div>
                <div class="w-full sm:w-64">
                    <input
                        type="text"
                        x-model="effectiveSearch"
                        placeholder="Saring hak akses efektif..."
                        class="w-full text-xs p-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="border border-[var(--border)] rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-[10px] uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-4 py-3">Nama Hak Akses (Permission)</th>
                            <th class="px-4 py-3">Identifier Teknis</th>
                            <th class="px-4 py-3">Sumber Otorisasi (Source)</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($effectivePermissions as $key => $item)
                            <tr
                                x-show="matchesEffective('{{ $item['name'] }}', '{{ addslashes($item['display_name']) }}', '{{ addslashes($item['source']) }}')"
                                class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-4 py-3 font-semibold text-[var(--foreground)]">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ $item['display_name'] }}</span>
                                        @if($item['is_protected'])
                                            <span class="px-1.5 py-0.2 text-[9px] font-bold rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300">
                                                PROTECTED
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-[var(--foreground-muted)]">
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-4 py-3">
                                    @if(str_contains($item['source'], 'SUPER ADMIN'))
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900">
                                            {{ $item['source'] }}
                                        </span>
                                    @elseif(str_contains($item['source'], 'DIRECT USER'))
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900">
                                            {{ $item['source'] }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">
                                            {{ $item['source'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold text-[11px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Diizinkan
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-xs text-[var(--foreground-muted)] italic">
                                    Pengguna ini belum memiliki hak akses efektif sama sekali. Silakan centang role pada formulir di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
