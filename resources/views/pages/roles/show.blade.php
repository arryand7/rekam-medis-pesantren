<x-app-layout>
    <x-slot name="title">Rincian Role: {{ $role->display_name ?? $role->name }} — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Top Breadcrumb & Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <a href="{{ route('roles.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Daftar Role
                </a>
                <div class="flex items-center gap-3 mt-1">
                    <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">
                        {{ $role->display_name ?? $role->name }}
                    </h1>
                    @if($role->isProtected())
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-md bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            SYSTEM PROTECTED ROLE
                        </span>
                    @endif
                </div>
                <div class="font-mono text-xs text-[var(--foreground-muted)] mt-0.5">
                    Identifier: <span class="text-[var(--primary)] font-semibold">{{ $role->name }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('roles.edit', $role->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Matrix Role
                </a>

                @if(! $role->isProtected() && $role->users_count === 0)
                    <form method="POST" action="{{ route('roles.destroy', $role->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini? Tindakan ini tidak dapat dibatalkan.');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-semibold rounded-xl transition-colors">
                            Hapus Role
                        </button>
                    </form>
                @endif
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

        <!-- Summary KPI Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase text-[var(--foreground-muted)] block">Total Pengguna</span>
                <div class="text-2xl font-extrabold text-[var(--foreground)] mt-1">{{ $role->users_count }} Pengguna</div>
            </div>
            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase text-[var(--foreground-muted)] block">Total Hak Akses</span>
                <div class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1">
                    {{ $role->name === 'super_admin' ? 'Universal Bypass' : $role->permissions->count() . ' Permissions' }}
                </div>
            </div>
            <div class="p-4 bg-[var(--surface)] rounded-2xl border border-[var(--border)] shadow-xs">
                <span class="text-xs font-semibold uppercase text-[var(--foreground-muted)] block">Tipe Kewenangan</span>
                <div class="text-sm font-bold text-[var(--foreground)] mt-1">
                    {{ $role->isProtected() ? 'Sistem Inti (Protected)' : 'Kustom / Aplikasi' }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Grouped Permissions List -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-[var(--foreground)]">Paket Hak Akses yang Dimiliki</h2>

                    @if($role->name === 'super_admin')
                        <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900 text-purple-900 dark:text-purple-200 text-xs">
                            <span class="font-bold">Hak Akses Universal (Super Admin):</span>
                            Role ini memiliki wewenang penuh tanpa batas ke seluruh modul, controller, dan route dalam aplikasi POSKESTREN.
                        </div>
                    @endif

                    @php
                        $rolePermNames = $role->permissions->pluck('name')->toArray();
                    @endphp

                    <div class="space-y-4">
                        @foreach($groupedPermissions as $groupKey => $group)
                            @php
                                $matchingPerms = $group['permissions']->filter(fn($p) => in_array($p->name, $rolePermNames, true));
                            @endphp
                            @if($matchingPerms->count() > 0 || $role->name === 'super_admin')
                                <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xs font-bold text-[var(--foreground)]">{{ $group['title'] }}</h3>
                                        <span class="text-[11px] font-semibold text-blue-600 dark:text-blue-400">
                                            {{ $role->name === 'super_admin' ? $group['permissions']->count() : $matchingPerms->count() }} Hak Akses
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach(($role->name === 'super_admin' ? $group['permissions'] : $matchingPerms) as $p)
                                            <span class="px-2 py-1 rounded-lg text-xs font-medium bg-[var(--surface)] text-[var(--foreground)] border border-[var(--border)] flex items-center gap-1.5">
                                                <span>{{ $p->display_name }}</span>
                                                @if($p->isProtected())
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500" title="Protected Permission"></span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if($role->permissions->count() === 0 && $role->name !== 'super_admin')
                            <div class="p-6 text-center text-xs text-[var(--foreground-muted)] italic bg-[var(--surface-muted)] rounded-xl">
                                Belum ada hak akses yang ditugaskan ke role ini. Silakan klik tombol "Edit Matrix Role" di atas.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Assigned Users -->
            <div class="space-y-4">
                <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-bold text-[var(--foreground)]">Pengguna dengan Role Ini</h2>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-[var(--surface-muted)] text-[var(--foreground)]">
                            {{ $role->users->count() }}
                        </span>
                    </div>

                    <div class="space-y-2 max-h-[500px] overflow-y-auto">
                        @forelse($role->users as $u)
                            <a href="{{ route('users.show', $u->id) }}" class="p-3 rounded-xl bg-[var(--surface-muted)] hover:bg-[var(--border)]/40 border border-[var(--border)] flex items-center justify-between transition-colors block">
                                <div class="min-w-0 pr-2">
                                    <div class="text-xs font-bold text-[var(--foreground)] truncate">{{ $u->name }}</div>
                                    <div class="text-[11px] text-[var(--foreground-muted)] font-mono truncate">{{ $u->email }}</div>
                                </div>
                                <span class="text-xs text-blue-600 dark:text-blue-400 font-semibold shrink-0">&rarr;</span>
                            </a>
                        @empty
                            <div class="text-xs text-[var(--foreground-muted)] italic text-center p-4">
                                Belum ada pengguna yang ditugaskan role ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
