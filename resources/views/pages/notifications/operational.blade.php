<x-app-layout>
    <x-slot name="title">Pusat Notifikasi Operasional</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <span class="p-2 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </span>
                    Pusat Notifikasi Operasional
                </h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Daftar instruksi perawatan dan batasan aktivitas untuk pembina asrama, guru, dan pengasuh.
                </p>
            </div>
            <div>
                <a href="{{ route('notifications.inbox.index') }}" class="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 transition">
                    Inbox Pribadi Petugas
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 font-semibold">
                        <tr>
                            <th class="py-3.5 px-4">Santri / Pasien</th>
                            <th class="py-3.5 px-4">Penerima</th>
                            <th class="py-3.5 px-4">Jenis Notifikasi</th>
                            <th class="py-3.5 px-4">Instruksi Praktis</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-700 dark:text-zinc-300">
                        @forelse($notifications as $notif)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition">
                                <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $notif->person?->full_name ?? '-' }}
                                    <div class="text-xs text-zinc-500 font-normal">Disiapkan: {{ $notif->prepared_at->format('d M Y H:i') }}</div>
                                </td>
                                <td class="py-3.5 px-4 capitalize font-medium">
                                    {{ str_replace('_', ' ', $notif->recipient_type) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-medium">
                                        {{ str_replace('_', ' ', $notif->notification_type) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $notif->payload_snapshot['rest_recommendation'] ?? ($notif->payload_snapshot['school_activity_status'] ?? '-') }}
                                    </div>
                                    <div class="text-zinc-500">
                                        {{ $notif->payload_snapshot['practical_instructions'] ?? ($notif->payload_snapshot['attendance_accommodation'] ?? '') }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($notif->status === 'acknowledged')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-medium">Acknowledged</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-medium">Prepared</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($notif->status === 'prepared')
                                        <form method="POST" action="{{ route('notifications.operational.acknowledge', $notif->id) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded shadow-sm transition">
                                                Konfirmasi Terima
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-zinc-500">Diterima oleh {{ $notif->acknowledgedBy?->name ?? 'Petugas' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada notifikasi operasional.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($notifications->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

