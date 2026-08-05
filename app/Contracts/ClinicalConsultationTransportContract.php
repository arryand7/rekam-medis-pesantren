<?php

namespace App\Contracts;

use App\Models\ClinicalConsultation;
use App\Models\ClinicalConsultationTransmission;
use App\Models\ClinicalConsultationVersion;
use App\Models\User;

interface ClinicalConsultationTransportContract
{
    /**
     * Transmit a finalized consultation summary to an external healthcare partner.
     */
    public function transmit(
        ClinicalConsultation $consultation,
        ClinicalConsultationVersion $version,
        ?User $actor = null
    ): ClinicalConsultationTransmission;
}
