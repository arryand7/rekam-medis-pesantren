<x-app-layout>
    <x-slot name="title">Rujukan {{ $referral->referral_number }} — SABIRA POSKESTREN</x-slot>

    <div class="space-y-6">
        @if($referral->medicalVisit && $referral->medicalVisit->patient)
            <!-- Patient Context Header -->
            <x-patient-context-header :patient="$referral->medicalVisit->patient" :visit="$referral->medicalVisit" />

            <!-- Visit Stage Stepper Navigation -->
            <x-visit-stage-nav :visit="$referral->medicalVisit" current="referrals" />
        @endif

        <!-- Referral Lifecycle Progress Stepper -->
        @php
            $refStages = [
                ['key' => 'prepared', 'label' => '1. Disiapkan', 'done' => in_array($referral->status, ['prepared', 'in_transit', 'arrived', 'handover_completed', 'accepted', 'completed', 'return_recorded', 'return_reviewed'])],
                ['key' => 'in_transit', 'label' => '2. Berangkat', 'done' => in_array($referral->status, ['in_transit', 'arrived', 'handover_completed', 'accepted', 'completed', 'return_recorded', 'return_reviewed'])],
                ['key' => 'arrived', 'label' => '3. Tiba di Faskes', 'done' => in_array($referral->status, ['arrived', 'handover_completed', 'accepted', 'completed', 'return_recorded', 'return_reviewed'])],
                ['key' => 'handover', 'label' => '4. Serah Terima', 'done' => in_array($referral->status, ['handover_completed', 'accepted', 'completed', 'return_recorded', 'return_reviewed'])],
                ['key' => 'accepted', 'label' => '5. Diterima Faskes', 'done' => in_array($referral->status, ['accepted', 'completed', 'return_recorded', 'return_reviewed'])],
                ['key' => 'returned', 'label' => '6. Kembali', 'done' => in_array($referral->status, ['return_recorded', 'return_reviewed', 'completed'])],
                ['key' => 'reviewed', 'label' => '7. Review Selesai', 'done' => in_array($referral->status, ['return_reviewed', 'completed'])],
            ];
        @endphp
        <div class="ui-card rounded-2xl p-4 shadow-xs overflow-x-auto">
            <div class="flex items-center gap-2 min-w-max text-xs font-semibold">
                @foreach($refStages as $rs)
                    <div class="ui-badge {{ $rs['done'] ? 'ui-badge-success' : 'ui-badge-neutral' }}">
                        <span>{{ $rs['done'] ? '✓' : '○' }}</span>
                        <span>{{ $rs['label'] }}</span>
                    </div>
                    @if(!$loop->last)
                        <span class="ui-text-tertiary">&rarr;</span>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Header -->
        <div class="ui-card flex items-start justify-between p-6 rounded-2xl shadow-xs">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-[var(--foreground)] font-mono">{{ $referral->referral_number }}</h1>
                    @if($referral->urgency === 'emergency')
                        <span class="ui-badge ui-badge-danger animate-pulse">
                            ⚠ DARURAT (EMERGENCY)
                        </span>
                    @elseif($referral->urgency === 'urgent')
                        <span class="ui-badge ui-badge-warning">
                            URGENT
                        </span>
                    @else
                        <span class="ui-badge ui-badge-success">
                            Rutin
                        </span>
                    @endif
                    <span class="ui-badge ui-badge-info uppercase">
                        {{ ucfirst(str_replace('_', ' ', $referral->status)) }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-[var(--foreground-muted)]">
                    Tujuan: <strong class="text-[var(--foreground)]">{{ $referral->partner?->name }}</strong>
                </p>
            </div>

            @if($referral->medical_visit_id)
                <a href="{{ route('visits.show', $referral->medical_visit_id) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[var(--surface-muted)] text-[var(--foreground)] border border-[var(--border)] hover:bg-[var(--surface)] transition-colors">
                    &larr; Workspace Kunjungan
                </a>
            @endif
        </div>


        @if(session('success'))
            <div class="ui-banner-success rounded-xl p-4">
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="ui-banner-danger rounded-xl p-4">
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- LEFT: Main Info -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Clinical Summary -->
                <div class="ui-card shadow-sm rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Alasan & Ringkasan Klinis</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs ui-text-muted">Alasan Rujukan</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 dark:text-white">{{ $referral->reason }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs ui-text-muted">Ringkasan Klinis</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $referral->clinical_summary }}</dd>
                        </div>
                        @if($referral->requested_service_or_department)
                            <div>
                                <dt class="text-xs ui-text-muted">Layanan / Poli yang Diminta</dt>
                                <dd class="mt-0.5 text-sm text-gray-900 dark:text-white">{{ $referral->requested_service_or_department }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Versioned Snapshot -->
                <div class="ui-card shadow-sm rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Dokumen Ringkasan Rujukan (Versioned)</h2>
                    @if($referral->versions->isEmpty())
                        <p class="text-sm ui-text-muted">Belum ada versi dokumen.</p>
                    @else
                        @foreach($referral->versions->sortByDesc('version_number') as $version)
                            <div class="border border-[var(--border-soft)] rounded-lg p-3 mb-3 text-xs">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold ui-text-secondary">Versi {{ $version->version_number }}</span>
                                    <span class="ui-text-tertiary font-mono">{{ substr($version->checksum, 0, 16) }}...</span>
                                </div>
                                <div class="ui-text-muted">
                                    Difinalisasi: {{ $version->finalized_at?->format('d M Y H:i') }}
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Transport -->
                <div class="ui-card shadow-sm rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Transportasi Rujukan</h2>
                    @if($referral->transports->isEmpty())
                        <p class="text-sm ui-text-muted mb-3">Transportasi belum diatur.</p>
                    @else
                        @foreach($referral->transports as $transport)
                            <div class="border border-[var(--border-soft)] rounded-lg p-3 mb-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $transport->transport_type)) }}</span>
                                    <span class="text-xs ui-text-tertiary">{{ $transport->vehicle_identifier }}</span>
                                    <span class="ui-badge ui-badge-info ml-auto">{{ ucfirst($transport->status) }}</span>
                                </div>
                                @if($transport->driver_name)
                                    <p class="text-xs ui-text-muted mt-1">Pengemudi: {{ $transport->driver_name }}</p>
                                @endif
                            </div>
                        @endforeach
                    @endif

                    @if(in_array($referral->status, ['prepared', 'approved', 'ready_to_depart']))
                        <form method="POST" action="{{ route('referrals.transport.store', $referral->id) }}" class="mt-3 space-y-3 border-t border-[var(--border-soft)] pt-3">
                            @csrf
                            <h3 class="text-xs font-medium ui-text-secondary">Atur Transportasi</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <select name="transport_type" class="ui-form-control w-full rounded border text-sm">
                                    <option value="school_vehicle">Kendaraan Pesantren</option>
                                    <option value="ambulance_partner">Ambulans Mitra</option>
                                    <option value="external_ambulance">Ambulans Eksternal</option>
                                    <option value="private_vehicle">Kendaraan Pribadi</option>
                                    <option value="other">Lainnya</option>
                                </select>
                                <input type="text" name="vehicle_identifier" placeholder="No. Polisi / ID Kendaraan"
                                       class="ui-form-control w-full rounded border text-sm">
                                <input type="text" name="driver_name" placeholder="Nama Pengemudi"
                                       class="ui-form-control w-full rounded border text-sm">
                                <input type="text" name="driver_contact" placeholder="Kontak Pengemudi"
                                       class="ui-form-control w-full rounded border text-sm">
                            </div>
                            <button type="submit" class="text-sm font-semibold px-3 py-1.5 bg-[var(--action-bg)] text-[var(--action-text)] rounded-lg hover:bg-[var(--action-hover)] transition-colors">
                                Atur Transportasi
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Companions -->
                <div class="ui-card shadow-sm rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Pendamping Rujukan</h2>
                    @if($referral->companions->isEmpty())
                        <p class="text-sm ui-text-muted mb-3">Pendamping belum ditugaskan.</p>
                    @else
                        @foreach($referral->companions as $companion)
                            <div class="border border-[var(--border-soft)] rounded-lg p-3 mb-2 text-sm flex items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $companion->name_snapshot }}</span>
                                    @if($companion->is_primary)
                                        <span class="ui-badge ui-badge-info ml-2">Utama</span>
                                    @endif
                                    <p class="text-xs ui-text-muted">{{ $companion->role_relationship }}</p>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if(in_array($referral->status, ['prepared', 'approved', 'ready_to_depart']))
                        <form method="POST" action="{{ route('referrals.companion.store', $referral->id) }}" class="mt-3 space-y-3 border-t border-[var(--border-soft)] pt-3">
                            @csrf
                            <h3 class="text-xs font-medium ui-text-secondary">Tugaskan Pendamping</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="text" name="name_snapshot" placeholder="Nama Pendamping" required
                                       class="ui-form-control w-full rounded border text-sm">
                                <input type="text" name="role_relationship" placeholder="Jabatan / Hubungan" required
                                       class="ui-form-control w-full rounded border text-sm">
                                <input type="text" name="phone" placeholder="Nomor HP"
                                       class="ui-form-control w-full rounded border text-sm">
                            </div>
                            <button type="submit" class="text-sm font-semibold px-3 py-1.5 bg-[var(--action-bg)] text-[var(--action-text)] rounded-lg hover:bg-[var(--action-hover)] transition-colors">
                                Tugaskan Pendamping
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Return from Referral -->
                @if($referral->returnRecord)
                    <div class="ui-card shadow-sm rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Kepulangan dari Rujukan</h2>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-xs ui-text-muted">Tanggal Kembali</dt>
                                <dd class="mt-0.5 font-medium text-gray-900 dark:text-white">{{ $referral->returnRecord->returned_at?->format('d M Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs ui-text-muted">Ringkasan Hasil Eksternal</dt>
                                <dd class="mt-0.5 text-gray-900 dark:text-white whitespace-pre-wrap">{{ $referral->returnRecord->external_outcome_summary }}</dd>
                            </div>
                            @if($referral->returnRecord->external_diagnosis_text)
                                <div>
                                    <dt class="text-xs ui-text-muted">Diagnosis Eksternal (Informasi — Belum Divalidasi Lokal)</dt>
                                    <dd class="mt-0.5 ui-text-secondary italic">{{ $referral->returnRecord->external_diagnosis_text }}</dd>
                                </div>
                            @endif
                            @if($referral->returnRecord->external_medication_instructions)
                                <div class="rounded bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-3">
                                    <dt class="text-xs font-semibold text-amber-700 dark:text-amber-300">⚠ Instruksi Obat Eksternal — Perlu Rekonsiliasi Lokal</dt>
                                    <dd class="mt-0.5 text-xs text-amber-700 dark:text-amber-400">{{ $referral->returnRecord->external_medication_instructions }}</dd>
                                </div>
                            @endif
                        </dl>

                        <!-- Local Return Review -->
                        @if($referral->returnRecord->reviews->isEmpty())
                            <div class="mt-5 border-t border-[var(--border-soft)] pt-4">
                                <h3 class="text-xs font-semibold ui-text-secondary mb-3">Tinjauan Klinis Lokal Poskestren</h3>
                                <form method="POST" action="{{ route('referrals.return-review.store', $referral->id) }}" class="space-y-3">
                                    @csrf
                                    <textarea name="review_summary" rows="3" required
                                              placeholder="Ringkasan tinjauan klinis lokal berdasarkan kondisi pasien saat ini..."
                                              class="ui-form-control w-full rounded border text-sm"></textarea>
                                    <select name="decision_type" required class="ui-form-control w-full rounded border text-sm">
                                        <option value="">— Keputusan Tindak Lanjut —</option>
                                        <option value="continue_poskestren_care">Lanjut Perawatan di Poskestren</option>
                                        <option value="continue_observation">Lanjut Observasi</option>
                                        <option value="follow_up_external">Kontrol ke Faskes Eksternal</option>
                                        <option value="rest_recommended">Istirahat Dianjurkan</option>
                                        <option value="return_to_activity_recommended">Kembali Beraktivitas</option>
                                        <option value="new_referral_recommended">Perlu Rujukan Baru</option>
                                        <option value="emergency_referral_required">Rujukan Darurat Segera</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                    <textarea name="medication_reconciliation_note" rows="2"
                                              placeholder="Catatan rekonsiliasi obat (jika ada)..."
                                              class="ui-form-control w-full rounded border text-sm"></textarea>
                                    <button type="submit" class="text-sm font-semibold px-4 py-2 bg-[var(--action-bg)] text-[var(--action-text)] rounded-lg hover:bg-[var(--action-hover)] transition-colors">
                                        Finalisasi Tinjauan Klinis Lokal
                                    </button>
                                </form>
                            </div>
                        @else
                            @foreach($referral->returnRecord->reviews as $review)
                                <div class="mt-4 border-t border-[var(--border-soft)] pt-3 text-sm">
                                    <p class="text-xs font-semibold ui-text-secondary mb-1">Keputusan Klinis Lokal Poskestren</p>
                                    <span class="ui-badge ui-badge-success">
                                        {{ ucfirst(str_replace('_', ' ', $review->decision_type)) }}
                                    </span>
                                    <p class="mt-2 ui-text-secondary">{{ $review->review_summary }}</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

            </div>

            <!-- RIGHT: Actions & Status Timeline -->
            <div class="space-y-5">

                <!-- Status Timeline -->
                <div class="ui-card shadow-sm rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Timeline Status</h2>
                    <div class="flow-root">
                        <ul class="space-y-3">
                            @foreach([
                                ['key' => 'initiated_at', 'label' => 'Dibuat', 'value' => $referral->initiated_at],
                                ['key' => 'departed_at', 'label' => 'Berangkat', 'value' => $referral->departed_at],
                                ['key' => 'arrived_at_destination', 'label' => 'Tiba di Tujuan', 'value' => $referral->arrived_at_destination],
                                ['key' => 'accepted_at_destination', 'label' => 'Diterima Faskes', 'value' => $referral->accepted_at_destination],
                                ['key' => 'returned_at', 'label' => 'Kembali', 'value' => $referral->returned_at],
                                ['key' => 'completed_at', 'label' => 'Selesai', 'value' => $referral->completed_at],
                            ] as $tl)
                                @if($tl['value'])
                                    <li class="flex items-start gap-2 text-sm">
                                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $tl['label'] }}</p>
                                            <p class="text-xs ui-text-muted">{{ $tl['value']->format('d M Y H:i') }}</p>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Status Events from Destination -->
                @if($referral->statusEvents->isNotEmpty())
                    <div class="ui-card shadow-sm rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Status Destinasi</h2>
                        @foreach($referral->statusEvents as $event)
                            <div class="text-sm border-b border-[var(--border-soft)] last:border-0 pb-2 mb-2">
                                <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $event->event_type)) }}</p>
                                <p class="text-xs ui-text-muted">{{ $event->occurred_at?->format('d M Y H:i') }} | {{ $event->contact_attribution }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="ui-card shadow-sm rounded-xl p-5 space-y-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Tindakan</h2>

                    @if(in_array($referral->status, ['prepared', 'approved', 'ready_to_depart']))
                        <form method="POST" action="{{ route('referrals.depart.store', $referral->id) }}">
                            @csrf
                            <input type="hidden" name="emergency_override_reason" value="">
                            <button type="submit" class="w-full text-sm px-4 py-2 bg-[var(--action-bg)] text-[var(--action-text)] rounded-lg hover:bg-[var(--action-hover)] transition-colors font-semibold">
                                Catat Keberangkatan
                            </button>
                        </form>
                    @endif

                    @if($referral->status === 'departed')
                        <form method="POST" action="{{ route('referrals.handover.store', $referral->id) }}">
                            @csrf
                            <button type="submit" class="w-full text-sm px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                                Catat Serah Terima Klinis
                            </button>
                        </form>
                    @endif

                    @if(in_array($referral->status, ['departed', 'arrived', 'accepted', 'under_external_care', 'return_planned']))
                        <!-- Record Status Event -->
                        <form method="POST" action="{{ route('referrals.status-event.store', $referral->id) }}" class="space-y-2">
                            @csrf
                            <select name="event_type" required class="ui-form-control w-full rounded border text-sm">
                                <option value="">— Perbarui Status Destinasi —</option>
                                <option value="arrived">Tiba di Tujuan</option>
                                <option value="accepted">Diterima Faskes</option>
                                <option value="declined">Ditolak Faskes</option>
                                <option value="under_external_care">Dalam Perawatan Eksternal</option>
                                <option value="return_planned">Kepulangan Direncanakan</option>
                            </select>
                            <input type="text" name="contact_attribution" placeholder="Nama pelapor (opsional)"
                                   class="ui-form-control w-full rounded border text-sm">
                            <button type="submit" class="w-full text-sm px-4 py-2 bg-[var(--action-bg)] text-[var(--action-text)] rounded-lg hover:bg-[var(--action-hover)] transition-colors font-semibold">
                                Catat Status
                            </button>
                        </form>

                        <!-- Record Return -->
                        @if(!$referral->returnRecord)
                            <div class="border-t border-[var(--border-soft)] pt-3">
                                <p class="text-xs font-medium ui-text-secondary mb-2">Catat Kepulangan Pasien</p>
                                <form method="POST" action="{{ route('referrals.return.store', $referral->id) }}" class="space-y-2">
                                    @csrf
                                    <textarea name="external_outcome_summary" rows="2" required
                                              placeholder="Ringkasan hasil perawatan dari faskes eksternal..."
                                              class="ui-form-control w-full rounded border text-sm"></textarea>
                                    <textarea name="restrictions_text" rows="1"
                                              placeholder="Pembatasan aktivitas / istirahat..."
                                              class="ui-form-control w-full rounded border text-sm"></textarea>
                                    <button type="submit" class="w-full text-sm px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                                        Catat Kepulangan
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif

                </div>

                <!-- Handover Log -->
                @if($referral->handovers->isNotEmpty())
                    <div class="ui-card shadow-sm rounded-xl p-5">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Log Serah Terima</h2>
                        @foreach($referral->handovers as $handover)
                            <div class="text-sm border-b border-[var(--border-soft)] last:border-0 pb-2 mb-2">
                                <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst($handover->status) }}</p>
                                <p class="text-xs ui-text-muted">
                                    {{ $handover->handed_over_at?->format('d M Y H:i') }} oleh {{ $handover->fromUser?->name }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
