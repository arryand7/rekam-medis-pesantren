<x-app-layout>
    <x-slot name="title">Konflik Identitas Integrasi</x-slot>
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <div>
                <a href="{{ route('integration.outbox.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-block">
                    &larr; Kembali ke Monitor Outbox
                </a>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    Konflik Identitas Integrasi
                </h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Daftar kegagalan pemetaan identitas person dari Gate yang memerlukan resolusi manual.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('integration.conflicts.index', ['status' => 'open']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $status === 'open' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-600' }}">
                    Open Conflicts
                </a>
                <a href="{{ route('integration.conflicts.index', ['status' => 'resolved']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border {{ $status === 'resolved' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-600' }}">
                    Resolved
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm text-left">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-600 dark:text-zinc-400 font-semibold">
                        <tr>
                            <th class="py-3.5 px-4">Person</th>
                            <th class="py-3.5 px-4">Tujuan</th>
                            <th class="py-3.5 px-4">Jenis Konflik</th>
                            <th class="py-3.5 px-4">Waktu Terjadi</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-700 dark:text-zinc-300">
                        @forelse($conflicts as $conflict)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/30 transition">
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $conflict->person?->full_name ?? '-' }}</span>
                                    <div class="text-xs text-zinc-500 font-mono">{{ $conflict->person_id }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-medium">
                                    {{ $conflict->destination }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-medium">
                                        {{ $conflict->conflict_type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    {{ $conflict->created_at?->format('d M Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($conflict->status === 'resolved')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-medium">Resolved</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-medium">Open</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($conflict->status === 'open')
                                        <form method="POST" action="{{ route('integration.conflicts.resolve', $conflict->id) }}" class="flex items-center justify-end gap-2">
                                            @csrf
                                            <input type="text" name="resolution_notes" placeholder="Catatan resolusi..." required class="text-xs p-1.5 rounded border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200">
                                            <button type="submit" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded shadow-sm transition">
                                                Resolve
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-zinc-500">{{ $conflict->resolution_notes }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                    Tidak ada konflik identitas tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($conflicts->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                    {{ $conflicts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

