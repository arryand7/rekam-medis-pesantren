<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating a referral.
 *
 * Security rules:
 * - No status field accepted (server-assigned: 'prepared')
 * - No initiated_by_id, initiated_at, approved_by_id (server-authoritative)
 * - No lock_version from client
 * - healthcare_partner_id validated but not allowed to change destination mid-request
 * - actor injected from Auth::user() in service layer only
 */
class StoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-referrals') ?? false;
    }

    public function rules(): array
    {
        return [
            'healthcare_partner_id' => ['required', 'string', 'exists:healthcare_partners,id'],
            'urgency' => ['required', 'string', 'in:routine,urgent,emergency'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
            'clinical_summary' => ['required', 'string', 'min:10', 'max:10000'],
            'requested_service_or_department' => ['nullable', 'string', 'max:500'],
            'recipient_contact_id' => ['nullable', 'string', 'exists:healthcare_partner_contacts,id'],
            'clinical_consultation_id' => ['nullable', 'string', 'exists:clinical_consultations,id'],
            'consultation_local_decision_id' => ['nullable', 'string', 'exists:consultation_local_decisions,id'],
            // Explicitly forbidden fields — actor, timestamps, and status MUST NOT come from client
        ];
    }

    public function messages(): array
    {
        return [
            'urgency.in' => 'Urgensi harus salah satu dari: routine, urgent, emergency.',
            'healthcare_partner_id.exists' => 'Fasilitas kesehatan tujuan tidak ditemukan.',
        ];
    }
}
