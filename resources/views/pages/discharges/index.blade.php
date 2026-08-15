<x-app-layout>
    <x-slot name="title">Daftar Kepulangan Kunjungan Medis</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kepulangan & Penutupan Kunjungan</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catatan kepulangan klinis, tindak lanjut, dan serah terima operasional</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('follow-up-plans.index') }}" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                    Antrean Follow-Up
                </a>
                <a href="{{ route('operational-handoffs.index') }}" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                    Handoff Operasional
                </a>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Kunjungan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pasien</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe & Destinasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aktivitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu Pulang</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse($discharges as $discharge)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold text-sky-600 dark:text-sky-400">
                                {{ $discharge->medicalVisit?->visit_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $discharge->medicalVisit?->patient?->person?->full_name ?? '-' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $discharge->medicalVisit?->patient?->patient_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $discharge->discharge_type) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $discharge->discharge_destination }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300 capitalize">
                                {{ str_replace('_', ' ', $discharge->activity_recommendation) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($discharge->status === 'finalized')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">Final</span>
                                @elseif($discharge->status === 'amended')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">Amandemen</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ ucfirst($discharge->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                {{ $discharge->finalized_at?->format('d/m/Y H:i') ?? $discharge->prepared_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <a href="{{ route('discharges.show', $discharge->id) }}" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Belum ada catatan kepulangan kunjungan medis.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($discharges->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $discharges->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
