<x-app-layout>
    <x-slot name="title">Detail Outbox Event</x-slot>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 pb-4">
            <div>
                <a href="{{ route('integration.outbox.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline mb-1 inline-block">
                    &larr; Kembali ke Monitor Outbox
                </a>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                    Outbox #{{ $event->id }}
                </h1>
            </div>
            @if(in_array($event->status, ['failed', 'dead_letter']))
                <form method="POST" action="{{ route('integration.outbox.retry', $event->id) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                        Retry Sekarang
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5 space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Metadata Event</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-zinc-500">Event Type:</span> <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $event->event_type }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Destination:</span> <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $event->destination }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Aggregate:</span> <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $event->aggregate_type }} (#{{ substr($event->aggregate_id, 0, 10) }}...)</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Idempotency Key:</span> <span class="font-mono text-xs text-zinc-800 dark:text-zinc-200">{{ $event->idempotency_key }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Status:</span> <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $event->status }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Attempt Count:</span> <span class="text-zinc-800 dark:text-zinc-200">{{ $event->attempt_count }}</span></div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5 space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Waktu & Audit</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-zinc-500">Dibuat Pada:</span> <span class="text-zinc-800 dark:text-zinc-200">{{ $event->created_at?->format('d M Y H:i:s') }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Tersedia Pada:</span> <span class="text-zinc-800 dark:text-zinc-200">{{ $event->available_at->format('d M Y H:i:s') }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Sent / Ack:</span> <span class="text-zinc-800 dark:text-zinc-200">{{ $event->acknowledged_at?->format('d M Y H:i:s') ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Correlation ID:</span> <span class="font-mono text-xs text-zinc-800 dark:text-zinc-200">{{ $event->correlation_id }}</span></div>
                </div>
            </div>
        </div>

        <!-- Payload Snapshot (JSON) -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5 space-y-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Payload Snapshot (Minimum Necessary)</h2>
            <pre class="bg-zinc-950 text-emerald-400 p-4 rounded-lg text-xs font-mono overflow-x-auto">{{ json_encode($event->payload_snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>

        <!-- Delivery Attempts -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5 space-y-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Riwayat Percobaan Pengiriman</h2>
            <div class="space-y-2">
                @forelse($event->deliveryAttempts as $attempt)
                    <div class="p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg flex items-center justify-between text-xs">
                        <div class="space-y-1">
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">Percobaan #{{ $attempt->attempt_number }} — {{ $attempt->result }}</span>
                            <div class="text-zinc-500">{{ $attempt->started_at->format('d M Y H:i:s') }} (Latency: {{ $attempt->latency_ms }} ms)</div>
                            @if($attempt->sanitized_error)
                                <div class="text-rose-600 dark:text-rose-400 font-mono">{{ $attempt->sanitized_error }}</div>
                            @endif
                        </div>
                        <div class="font-mono text-zinc-600 dark:text-zinc-400">
                            HTTP {{ $attempt->http_status_code ?? '-' }}
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-zinc-500">Belum ada riwayat percobaan pengiriman.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

