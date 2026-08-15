<x-app-layout>
    <x-slot name="title">Manajemen Role & Hak Akses</x-slot>

    <div class="space-y-6">
        <!-- Header & Action Strip -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Manajemen Role & Peran</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900">
                        {{ $roles->count() }} Role Aktif
                    </span>
                </div>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">
                    Definisi paket hak akses (permissions) server-side berbasis Policy. Role admin tidak otomatis mendapat akses data rekam medis.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Role Baru</span>
                </a>
            </div>
        </div>

        <!-- Feedback Messages -->
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Search Bar -->
        <div class="bg-[var(--surface)] p-4 rounded-2xl border border-[var(--border)] shadow-xs">
            <form method="GET" action="{{ route('roles.index') }}" class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="relative flex-1 w-full">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama role, tampilan, atau deskripsi..."
                        class="w-full text-xs p-2.5 pl-9 rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 text-[var(--foreground-muted)] absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors w-full sm:w-auto">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('roles.index') }}" class="px-3 py-2.5 text-xs text-[var(--foreground-muted)] hover:underline">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Role Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($roles as $role)
                <div class="bg-[var(--surface)] rounded-2xl border border-[var(--border)] p-5 shadow-xs flex flex-col justify-between hover:border-blue-500/50 transition-colors">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h2 class="text-base font-bold text-[var(--foreground)] tracking-tight">
                                    {{ $role->display_name ?? $role->name }}
                                </h2>
                                <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-[var(--surface-muted)] text-[var(--foreground-muted)] border border-[var(--border)] inline-block mt-0.5">
                                    {{ $role->name }}
                                </span>
                            </div>
                            @if($role->isProtected())
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900 shrink-0 flex items-center gap-1" title="Role inti sistem dengan proteksi khusus">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    PROTECTED
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-[var(--foreground-muted)] line-clamp-2 min-h-[32px]">
                            {{ $role->description ?? 'Tidak ada deskripsi peran.' }}
                        </p>

                        <!-- Stats Strip -->
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[var(--border)]">
                            <div class="p-2.5 rounded-xl bg-[var(--surface-muted)] text-center">
                                <span class="text-[10px] uppercase font-semibold text-[var(--foreground-muted)] block">Pengguna</span>
                                <span class="text-sm font-bold text-[var(--foreground)]">{{ $role->users_count }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[var(--surface-muted)] text-center">
                                <span class="text-[10px] uppercase font-semibold text-[var(--foreground-muted)] block">Hak Akses</span>
                                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                    {{ $role->name === 'super_admin' ? 'SEMUA (' . $totalPermissions . ')' : $role->permissions->count() }}
                                </span>
                            </div>
                        </div>

                        <!-- Sample Permissions -->
                        <div class="space-y-1 pt-1">
                            <span class="text-[10px] uppercase font-semibold text-[var(--foreground-muted)] block">Cuplikan Hak Akses:</span>
                            <div class="flex flex-wrap gap-1">
                                @if($role->name === 'super_admin')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                                        * Super Admin Universal Bypass
                                    </span>
                                @else
                                    @forelse($role->permissions->take(4) as $p)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)]">
                                            {{ $p->name }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-[var(--foreground-muted)] italic">Belum ada hak akses</span>
                                    @endforelse
                                    @if($role->permissions->count() > 4)
                                        <span class="px-1.5 py-0.5 text-[10px] text-[var(--foreground-muted)]">
                                            +{{ $role->permissions->count() - 4 }} lainnya
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-[var(--border)] mt-4 flex items-center justify-between gap-2">
                        <a href="{{ route('roles.show', $role->id) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Lihat Rincian &rarr;
                        </a>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('roles.edit', $role->id) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[var(--surface-muted)] hover:bg-[var(--border)] text-[var(--foreground)] transition-colors" title="Edit Matrix Hak Akses">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-[var(--surface)] rounded-2xl border border-[var(--border)] text-[var(--foreground-muted)] text-sm">
                    Tidak ditemukan role yang sesuai dengan filter pencarian.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
