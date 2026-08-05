<x-app-layout>
    <x-slot name="title">Akun Pengguna (User Accounts) — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Kelola Akun Pengguna (User Accounts)</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Akun login aplikasi. Deaktivasi akun tidak menghapus identitas Person atau riwayat rekam medis.</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)]">
                    Access Control Active
                </span>
            </div>
        </div>

        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Pengguna & Email</th>
                            <th class="px-6 py-3.5">Terhubung Person</th>
                            <th class="px-6 py-3.5">Role Aplikatif</th>
                            <th class="px-6 py-3.5">Status Akun</th>
                            <th class="px-6 py-3.5">Preferensi Tema</th>
                            <th class="px-6 py-3.5">Login Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($users as $u)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-[var(--foreground)]">{{ $u->name }}</div>
                                    <div class="text-xs text-[var(--foreground-muted)] font-mono">{{ $u->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($u->person)
                                        <span class="font-medium text-[var(--foreground)]">{{ $u->person->name }}</span>
                                        <div class="text-[var(--foreground-muted)] font-mono text-[11px]">{{ $u->person->user_type }}</div>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400 italic">Akun Teknis Murni (Tanpa Person)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($u->roles as $r)
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                                                {{ $r->display_name ?? $r->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-[var(--foreground-muted)]">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($u->is_active)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs capitalize text-[var(--foreground-muted)]">
                                    {{ $u->theme_preference }}
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                    {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Belum Pernah' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    Belum ada akun User terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
