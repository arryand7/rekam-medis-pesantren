<x-app-layout>
    <x-slot name="title">Detail Kepulangan {{ $discharge->medicalVisit?->visit_number }}</x-slot>

    <div class="space-y-6">
        <!-- Top Bar -->
        <div class="flex items-start justify-between">
            <div>
                <a href="{{ route('discharges.index') }}" class="text-sm text-sky-600 dark:text-sky-400 hover:underline">← Daftar Kepulangan</a>
                <div class="mt-2 flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Kepulangan Klinis</h1>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
                        {{ $discharge->medicalVisit?->visit_number }}
                    </span>
                    @if($discharge->status === 'finalized')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                            FINAL
                        </span>
                    @elseif($discharge->status === 'amended')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                            AMENDED
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ strtoupper($discharge->status) }}
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pasien: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $discharge->medicalVisit?->patient?->person?->full_name }}</span> |
                    Destinasi: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $discharge->discharge_destination }}</span>
                </p>
            </div>
            <div>
                <a href="{{ route('visits.discharge', $discharge->medical_visit_id) }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-medium shadow-sm transition">
                    Buka Workspace
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Discharge Details & Sub-modules -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Clinical Summary Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Ringkasan Medis Kepulangan</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400 font-medium">Tipe Kepulangan</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white font-semibold capitalize">{{ str_replace('_', ' ', $discharge->discharge_type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400 font-medium">Kondisi Akhir</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white font-semibold">{{ $discharge->final_condition }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400 font-medium">Ringkasan Klinis</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white whitespace-pre-wrap bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">{{ $discharge->clinical_summary }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400 font-medium">Rekomendasi Aktivitas</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white font-semibold capitalize">{{ str_replace('_', ' ', $discharge->activity_recommendation) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400 font-medium">Anjuran Istirahat</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $discharge->rest_recommendation ?? '-' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400 font-medium">Catatan Batasan</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $discharge->restriction_notes ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Follow-Up Plans -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Rencana Tindak Lanjut (Follow-Up)</h2>
                    </div>

                    <div class="space-y-3">
                        @forelse($discharge->followUpPlans as $plan)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg border border-gray-200 dark:border-gray-600 flex items-start justify-between text-xs">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $plan->follow_up_type) }}</div>
                                    <div class="text-gray-600 dark:text-gray-300 mt-1">{{ $plan->instructions }}</div>
                                    <div class="text-gray-400 mt-1">Target: {{ $plan->due_at?->format('d/m/Y H:i') ?? 'Fleksibel' }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $plan->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                                        {{ ucfirst($plan->status) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tidak ada rencana tindak lanjut terdaftar.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Operational Handoffs Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Serah Terima Operasional Internal (Handoff)</h2>
                    <div class="space-y-3">
                        @forelse($discharge->operationalHandoffs as $handoff)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg border border-gray-200 dark:border-gray-600 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-900 dark:text-white capitalize">Penerima: {{ str_replace('_', ' ', $handoff->recipient_type) }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $handoff->isAcknowledged() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $handoff->isAcknowledged() ? 'Dikonfirmasi' : 'Menunggu Konfirmasi' }}
                                    </span>
                                </div>
                                <div class="text-gray-600 dark:text-gray-300">Tujuan: {{ $handoff->purpose }}</div>
                                <div class="text-gray-400">Disiapkan: {{ $handoff->prepared_at->format('d/m/Y H:i') }} oleh {{ $handoff->preparedBy?->name ?? 'Sistem' }}</div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada serah terima operasional internal.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Col: Document & Versions -->
            <div class="space-y-6">
                <!-- Document & Checksums -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                    <h3 class="text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Dokumen Privat & Versi</h3>

                    <div class="space-y-3">
                        @foreach($discharge->versions as $version)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-xs space-y-2 border border-gray-200 dark:border-gray-600">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-900 dark:text-white">Versi {{ $version->version_number }}</span>
                                    <span class="text-gray-500">{{ $version->finalized_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="font-mono text-[10px] text-gray-500 break-all">
                                    SHA-256: {{ substr($version->checksum, 0, 16) }}...
                                </div>

                                <div class="pt-2 flex gap-2">
                                    @if($version->hasDocument())
                                        <a href="{{ route('discharges.document.download', [$discharge->id, $version->id]) }}" class="w-full py-1.5 px-3 bg-sky-600 hover:bg-sky-700 text-white rounded text-center text-xs font-medium">
                                            Unduh Ringkasan Pulang
                                        </a>
                                    @else
                                        <form action="{{ route('discharges.document.generate', [$discharge->id, $version->id]) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full py-1.5 px-3 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 text-gray-800 dark:text-gray-200 rounded text-xs font-medium">
                                                Generate Dokumen Privat
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
