<x-app-layout>
    <x-slot name="title">Integration Outbox Monitor — SABIRA POSKESTREN</x-slot>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-5">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <span class="p-2 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/></svg>
                    </span>
                    Integration Outbox Monitor
                </h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Pemantauan antrean pengiriman event outbound integrasi (Absensi, Asrama, Sistem Eksternal).
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('integration.attendance.status') }}" class="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 transition">
                    Status Konektor Absensi
                </a>
                <a href="{{ route('integration.conflicts.index') }}" class="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-sm font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 transition">
                    Konflik Identitas
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter bar -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <form method="GET" action="{{ route('integration.outbox.index') }}" class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400 mb-1">Status</label>
                    <select name="status" class="w-full text-sm rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 p-2">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="processing" @selected($status === 'processing')>Processing</option>
                        <option value="acknowledged" @selected($status === 'acknowledged')>Acknowledged (Sukses)</option>
                        <option value="failed" @selected($status === 'failed')>Failed (Menunggu Retry)</option>
                        <option value="dead_letter" @selected($status === 'dead_letter')>Dead Letter (Gagal Permanen)</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400 mb-1">Tujuan</label>
                    <select name="destination" class="w-full text-sm rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 p-2">
                        <option value="">Semua Tujuan</option>
                        <option value="attendance_system" @selected($destination === 'attendance_system')>SABIRA Absensi</option>
                        <option value="dorm_system" @selected($destination === 'dorm_system')>Sistem Asrama</option>
                    </select>
                </div>
                <div class="self-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 font-semibold">
                        <tr>
                            <th class="py-3.5 px-4">Event ID / Tipe</th>
                            <th class="py-3.5 px-4">Destinasi</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Percobaan</th>
                            <th class="py-3.5 px-4">Tersedia Pada</th>
                            <th class="py-3.5 px-4">Waktu Sukses</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-700 dark:text-zinc-300">
                        @forelse($events as $event)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition">
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">{{ substr($event->id, 0, 13) }}...</span>
                                    <div class="text-xs text-zinc-500 font-medium">{{ $event->event_type }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-medium">
                                    {{ $event->destination }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($event->status === 'acknowledged')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-medium">Acknowledged</span>
                                    @elseif($event->status === 'pending')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-medium">Pending</span>
                                    @elseif($event->status === 'processing')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-medium">Processing</span>
                                    @elseif($event->status === 'failed')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 font-medium">Failed</span>
                                    @elseif($event->status === 'dead_letter')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-medium">Dead Letter</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300 font-medium">{{ $event->status }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    {{ $event->attempt_count }} kali
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    {{ $event->available_at->format('d M Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    {{ $event->acknowledged_at?->format('d M Y H:i') ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a href="{{ route('integration.outbox.show', $event->id) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                        Detail
                                    </a>
                                    @if(in_array($event->status, ['failed', 'dead_letter']))
                                        <form method="POST" action="{{ route('integration.outbox.retry', $event->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-amber-600 dark:text-amber-400 hover:underline">
                                                Retry
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada data event outbox integrasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($events->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

