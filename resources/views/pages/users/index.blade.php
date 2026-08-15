<x-app-layout>
    <x-slot name="title">Kelola Akun Pengguna — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Header & Breadcrumb -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Kelola Akun Pengguna & Hak Akses</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900">
                        {{ $users->total() }} Pengguna Terdaftar
                    </span>
                </div>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">
                    Kelola penugasan role aplikatif, hak akses langsung (exception), dan preview effective permissions per pengguna.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)]">
                    Gate Entitlement & Local RBAC
                </span>
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

        <!-- Filter & Search Bar -->
        <div class="bg-[var(--surface)] p-4 rounded-2xl border border-[var(--border)] shadow-xs">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="relative flex-1 w-full">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama pengguna, email, NIS/NIP, atau NIK..."
                        class="w-full text-xs p-2.5 pl-9 rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 text-[var(--foreground-muted)] absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div class="w-full sm:w-48">
                    <select
                        name="role"
                        class="w-full text-xs p-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                                {{ $r->display_name ?? $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors w-full sm:w-auto">
                        Cari
                    </button>
                    @if(request('search') || request('role'))
                        <a href="{{ route('users.index') }}" class="px-3 py-2.5 text-xs text-[var(--foreground-muted)] hover:underline">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Pengguna & Email</th>
                            <th class="px-6 py-3.5">Terhubung Person</th>
                            <th class="px-6 py-3.5">Role Aplikatif</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($users as $u)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('users.show', $u->id) }}" class="font-bold text-sm text-[var(--foreground)] hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $u->name }}
                                    </a>
                                    <div class="text-xs text-[var(--foreground-muted)] font-mono">{{ $u->email }}</div>
                                    @if($u->permissions->count() > 0)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.2 rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 mt-1" title="Pengguna memiliki hak akses langsung (exception)">
                                            +{{ $u->permissions->count() }} Direct Exception
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($u->person)
                                        <span class="font-medium text-[var(--foreground)]">{{ $u->person->name }}</span>
                                        <div class="text-[var(--foreground-muted)] font-mono text-[11px]">{{ $u->person->user_type }} ({{ $u->person->nis_nip ?? 'Tanpa ID' }})</div>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400 italic">Akun Teknis Murni</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($u->roles as $r)
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $r->name === 'super_admin' ? 'bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900' : 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900' }}">
                                                {{ $r->display_name ?? $r->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-[var(--foreground-muted)] italic">Tanpa Role</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($u->is_active)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('users.show', $u->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[var(--surface-muted)] hover:bg-[var(--border)] text-[var(--foreground)] transition-colors">
                                        <span>Kelola Akses</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-[var(--foreground-muted)] text-sm">
                                    Tidak ada pengguna yang cocok dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-[var(--border)] bg-[var(--surface)]">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
