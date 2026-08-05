<x-app-layout>
    <x-slot name="title">Role & Permission — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Manajemen Role & Hak Akses (Permission)</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Otorisasi server-side berbasis Policy. Role admin tidak otomatis mendapat akses data rekam medis.</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900">
                    Server-Side Policy Enforcement
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Roles List -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                <h2 class="text-lg font-bold text-[var(--foreground)]">Peran Aplikatif (Roles)</h2>
                <div class="space-y-3">
                    @forelse($roles as $role)
                        <div class="p-4 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-[var(--foreground)]">{{ $role->display_name ?? $role->name }}</span>
                                <span class="font-mono text-xs px-2 py-0.5 rounded bg-[var(--surface)] text-[var(--primary)] border border-[var(--border)]">
                                    {{ $role->name }}
                                </span>
                            </div>
                            <p class="text-xs text-[var(--foreground-muted)]">{{ $role->description }}</p>
                            <div class="pt-2 flex flex-wrap gap-1">
                                @forelse($role->permissions as $p)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300">
                                        {{ $p->name }}
                                    </span>
                                @empty
                                    <span class="text-[11px] text-[var(--foreground-muted)]">Belum ada permission khusus</span>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--foreground-muted)]">Belum ada role terdaftar.</div>
                    @endforelse
                </div>
            </div>

            <!-- Permissions List -->
            <div class="bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs space-y-4">
                <h2 class="text-lg font-bold text-[var(--foreground)]">Daftar Hak Akses (Permissions)</h2>
                <div class="space-y-2">
                    @forelse($permissions as $perm)
                        <div class="p-3 rounded-xl bg-[var(--surface-muted)] border border-[var(--border)] flex items-center justify-between">
                            <div>
                                <div class="font-mono text-xs font-bold text-[var(--foreground)]">{{ $perm->name }}</div>
                                <div class="text-xs text-[var(--foreground-muted)]">{{ $perm->description ?? $perm->display_name }}</div>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--foreground-muted)]">Belum ada permission terdaftar.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
