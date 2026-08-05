<x-app-layout>
    <x-slot name="title">Rujukan Eksternal — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Antrian Rujukan Eksternal</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Daftar rujukan aktif dan riwayat rujukan santri ke fasilitas kesehatan mitra
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-700">
                <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-700">
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Referral Status Legend -->
        <div class="flex flex-wrap gap-2 text-xs">
            @foreach(['prepared' => 'yellow', 'approved' => 'blue', 'ready_to_depart' => 'indigo', 'departed' => 'purple', 'arrived' => 'cyan', 'accepted' => 'teal', 'under_external_care' => 'orange', 'return_planned' => 'lime', 'returned' => 'emerald', 'completed' => 'green', 'cancelled' => 'red', 'declined_by_destination' => 'rose'] as $status => $color)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-800 dark:text-{{ $color }}-300">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
            @endforeach
        </div>

        <!-- Referral List -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            @if($referrals->isEmpty())
                <div class="p-12 text-center">
                    <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Belum ada rujukan</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rujukan dibuat dari halaman detail kunjungan medis santri.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nomor Rujukan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pasien</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tujuan Faskes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Urgensi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($referrals as $referral)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400">
                                        {{ $referral->referral_number }}
                                    </span>
                                    @if($referral->urgency === 'emergency')
                                        <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">DARURAT</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $referral->medicalVisit?->patient?->person?->name ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $referral->partner?->name ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $urgencyColor = match($referral->urgency) {
                                            'emergency' => 'red',
                                            'urgent' => 'orange',
                                            default => 'green',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $urgencyColor }}-100 text-{{ $urgencyColor }}-800 dark:bg-{{ $urgencyColor }}-900/30 dark:text-{{ $urgencyColor }}-300">
                                        {{ ucfirst($referral->urgency) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ ucfirst(str_replace('_', ' ', $referral->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $referral->initiated_at?->format('d M Y') ?? $referral->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('referrals.show', $referral->id) }}"
                                       class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        Detail →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $referrals->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
