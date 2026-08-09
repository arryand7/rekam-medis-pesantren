<x-app-layout>
    <x-slot name="title">Inbox Notifikasi Petugas — SABIRA POSKESTREN</x-slot>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <div>
                <a href="{{ route('notifications.operational.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-block">
                    &larr; Pusat Notifikasi Operasional
                </a>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    Inbox Notifikasi Petugas
                </h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Pengingat internal (kontrol ulang berjadwal, serah terima tertunda, dan alert sistem).
                </p>
            </div>
            <form method="POST" action="{{ route('notifications.inbox.read-all') }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-600 transition">
                    Tandai Semua Dibaca
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-3">
            @forelse($notifications as $item)
                <div class="p-4 rounded-xl border {{ $item->isRead() ? 'bg-white dark:bg-zinc-800/60 border-zinc-200 dark:border-zinc-700' : 'bg-blue-50/50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800' }} shadow-sm transition flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase text-blue-600 dark:text-blue-400">{{ str_replace('_', ' ', $item->notification_type) }}</span>
                            <span class="text-xs text-zinc-400">• {{ $item->created_at?->diffForHumans() }}</span>
                        </div>
                        <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $item->title }}</h3>
                        <p class="text-xs text-zinc-600 dark:text-zinc-300">{{ $item->body }}</p>
                    </div>
                    @if(! $item->isRead())
                        <form method="POST" action="{{ route('notifications.inbox.read', $item->id) }}">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 text-center text-zinc-500 dark:text-zinc-400">
                    Tidak ada notifikasi di inbox Anda.
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="p-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

