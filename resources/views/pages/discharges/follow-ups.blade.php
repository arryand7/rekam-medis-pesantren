<x-app-layout>
    <x-slot name="title">Antrean Tindak Lanjut (Follow-Up Plans)</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('discharges.index') }}" class="text-sm text-sky-600 dark:text-sky-400 hover:underline">← Daftar Kepulangan</a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Antrean Rencana Tindak Lanjut (Follow-Up)</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Daftar jadwal kontrol ulang, evaluasi aktivitas, dan peninjauan pengobatan santri</p>
            </div>
        </div>

        @if(session('status'))
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pasien & Kunjungan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe & Target Jadwal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Instruksi Tindak Lanjut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $plan->visitDischarge?->medicalVisit?->patient?->person?->full_name ?? '-' }}</div>
                                <div class="text-xs font-mono text-sky-600 dark:text-sky-400">{{ $plan->visitDischarge?->medicalVisit?->visit_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-900 dark:text-white capitalize font-semibold">{{ str_replace('_', ' ', $plan->follow_up_type) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $plan->due_at?->format('d/m/Y H:i') ?? 'Fleksibel' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $plan->instructions }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plan->status === 'completed')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        ✓ Selesai
                                    </span>
                                @elseif($plan->status === 'cancelled')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        Batal
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-300">
                                        Direncanakan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                @if($plan->status === 'planned')
                                    <form action="{{ route('follow-up-plans.complete', $plan->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition shadow-sm">
                                            Selesaikan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">
                                        {{ $plan->completed_at?->format('d/m/Y H:i') ?? '-' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Belum ada rencana tindak lanjut terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($plans->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $plans->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
