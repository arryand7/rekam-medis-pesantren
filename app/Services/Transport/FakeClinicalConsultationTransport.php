<?php

namespace App\Services\Transport;

use App\Contracts\ClinicalConsultationTransportContract;
use App\Models\ClinicalConsultation;
use App\Models\ClinicalConsultationTransmission;
use App\Models\ClinicalConsultationVersion;
use App\Models\User;
use Illuminate\Support\Str;

class FakeClinicalConsultationTransport implements ClinicalConsultationTransportContract
{
    public function transmit(
        ClinicalConsultation $consultation,
        ClinicalConsultationVersion $version,
        ?User $actor = null
    ): ClinicalConsultationTransmission {
        return ClinicalConsultationTransmission::create([
            'clinical_consultation_id' => $consultation->id,
            'clinical_consultation_version_id' => $version->id,
            'healthcare_partner_id' => $consultation->healthcare_partner_id,
            'recipient_contact_id' => $consultation->recipient_contact_id,
            'channel' => 'fake_transport',
            'status' => 'sent',
            'idempotency_key' => 'IDEMP-TRANSMIT-'.$consultation->id.'-V'.$version->version_number,
            'external_reference' => 'EXT-REF-'.strtoupper(Str::random(8)),
            'attempted_at' => now(),
            'sent_at' => now(),
            'initiated_by_id' => $actor?->id,
            'correlation_id' => Str::uuid()->toString(),
        ]);
    }
}
