<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('record-return-from-referral') ?? false;
    }

    public function rules(): array
    {
        return [
            'external_outcome_summary' => ['required', 'string', 'min:5', 'max:5000'],
            'external_diagnosis_text' => ['nullable', 'string', 'max:2000'],
            'external_procedures_text' => ['nullable', 'string', 'max:2000'],
            'external_medication_instructions' => ['nullable', 'string', 'max:2000'],
            'restrictions_text' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'follow_up_facility' => ['nullable', 'string', 'max:500'],
            'return_transport_notes' => ['nullable', 'string', 'max:1000'],
            'accompanied_by_notes' => ['nullable', 'string', 'max:500'],
            'documents_received_notes' => ['nullable', 'string', 'max:1000'],
            // Forbidden: returned_at, recorded_by_id, status (all server-authoritative)
            // Forbidden: referral_id (bound via route)
        ];
    }
}
