<x-app-layout>
    <x-slot name="title">Rujukan Eksternal</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[var(--foreground)]">Antrian Rujukan Eksternal</h1>
                <p class="mt-1 text-sm ui-text-muted">
                    Daftar rujukan aktif dan riwayat rujukan santri ke fasilitas kesehatan mitra
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="ui-banner-success rounded-xl p-4">
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="ui-banner-danger rounded-xl p-4">
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Referral Status Legend -->
        @php
            $referralStatusStyles = [
                'prepared' => 'ui-badge-warning',
                'approved' => 'ui-badge-info',
                'ready_to_depart' => 'ui-badge-info',
                'departed' => 'ui-badge-info',
                'arrived' => 'ui-badge-info',
                'accepted' => 'ui-badge-success',
                'under_external_care' => 'ui-badge-warning',
                'return_planned' => 'ui-badge-warning',
                'returned' => 'ui-badge-success',
                'completed' => 'ui-badge-success',
                'cancelled' => 'ui-badge-danger',
                'declined_by_destination' => 'ui-badge-danger',
            ];
        @endphp
        <div class="flex flex-wrap gap-2" aria-label="Legenda status rujukan">
            @foreach($referralStatusStyles as $status => $statusStyle)
                <span class="ui-badge {{ $statusStyle }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
            @endforeach
        </div>

        <!-- Referral List -->
        <div class="ui-card shadow-sm rounded-xl overflow-hidden">
            @if($referrals->isEmpty())
                <div class="p-12 text-center">
                    <div class="mx-auto h-12 w-12 ui-text-tertiary mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-[var(--foreground)]">Belum ada rujukan</h3>
                    <p class="mt-1 text-sm ui-text-muted">Rujukan dibuat dari halaman detail kunjungan medis santri.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-[var(--border-soft)]">
                    <thead class="ui-surface-subtle">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs ui-table-heading uppercase tracking-wider">Nomor Rujukan</th>
                            <th class="px-6 py-3 text-left text-xs ui-table-heading uppercase tracking-wider">Pasien</th>
                            <th class="px-6 py-3 text-left text-xs ui-table-heading uppercase tracking-wider">Tujuan Faskes</th>
                            <th class="px-6 py-3 text-left text-xs ui-table-heading uppercase tracking-wider">Urgensi</th>
                            <th class="px-6 py-3 text-left text-xs ui-table-heading uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs ui-table-heading uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-[var(--surface)] divide-y divide-[var(--border-soft)]">
                        @foreach($referrals as $referral)
                            <tr class="hover:bg-[var(--surface-subtle)] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="ui-link text-sm font-mono font-semibold">
                                        {{ $referral->referral_number }}
                                    </span>
                                    @if($referral->urgency === 'emergency')
                                        <span class="ml-2 ui-badge ui-badge-danger">DARURAT</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-[var(--foreground)]">
                                        {{ $referral->medicalVisit?->patient?->person?->name ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm ui-text-secondary">{{ $referral->partner?->name ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $urgencyStyle = match($referral->urgency) {
                                            'emergency' => 'ui-badge-danger',
                                            'urgent' => 'ui-badge-warning',
                                            default => 'ui-badge-success',
                                        };
                                    @endphp
                                    <span class="ui-badge {{ $urgencyStyle }}">
                                        {{ ucfirst($referral->urgency) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="ui-badge {{ $referralStatusStyles[$referral->status] ?? 'ui-badge-neutral' }}">
                                        {{ ucfirst(str_replace('_', ' ', $referral->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm ui-text-muted">
                                    {{ $referral->initiated_at?->format('d M Y') ?? $referral->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('referrals.show', $referral->id) }}"
                                       class="ui-link hover:underline font-semibold">
                                        Detail →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="px-6 py-4 border-t border-[var(--border-soft)]">
                    {{ $referrals->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
