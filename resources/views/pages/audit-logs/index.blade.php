<x-app-layout>
    <x-slot name="title">Audit Log System</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-[var(--surface)] p-6 rounded-2xl border border-[var(--border)] shadow-xs">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)] tracking-tight">Log Audit Sistem (Append-Only)</h1>
                <p class="text-sm text-[var(--foreground-muted)] mt-1">Catatan riwayat mutasi identitas, role, permission, dan simulasi dry-run. Tidak dapat diubah atau dihapus.</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900">
                    Immutable Audit Active
                </span>
            </div>
        </div>

        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[var(--foreground)]">
                    <thead class="bg-[var(--surface-muted)] text-xs uppercase font-semibold text-[var(--foreground-muted)] border-b border-[var(--border)]">
                        <tr>
                            <th class="px-6 py-3.5">Waktu & Correlation ID</th>
                            <th class="px-6 py-3.5">Aktor (Actor)</th>
                            <th class="px-6 py-3.5">Aksi (Action)</th>
                            <th class="px-6 py-3.5">Subjek / Target</th>
                            <th class="px-6 py-3.5">Detail / Alasan</th>
                            <th class="px-6 py-3.5">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse($auditLogs as $log)
                            <tr class="hover:bg-[var(--surface-muted)]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-[var(--foreground)] text-xs">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                                    <div class="text-[11px] text-[var(--foreground-muted)] font-mono">{{ Str::limit($log->correlation_id, 8) }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-[var(--foreground)]">
                                    {{ $log->actor_name }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-[var(--foreground-muted)]">
                                    {{ $log->subject_type }} {{ $log->subject_id ? "({$log->subject_id})" : '' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--foreground-muted)]">
                                    {{ $log->reason ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-[var(--foreground-muted)]">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-[var(--foreground-muted)]">
                                    Belum ada log audit tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
