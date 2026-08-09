<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralReturnReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('review-return-from-referral') ?? false;
    }

    public function rules(): array
    {
        return [
            'review_summary' => ['required', 'string', 'min:5', 'max:5000'],
            'decision_type' => [
                'required',
                'string',
                'in:continue_poskestren_care,continue_observation,follow_up_external,rest_recommended,return_to_activity_recommended,new_referral_recommended,emergency_referral_required,other',
            ],
            'medication_reconciliation_note' => ['nullable', 'string', 'max:2000'],
            // Forbidden: local_reviewer_id, finalized_at, status (all server-authoritative)
            // Review does NOT create discharge — enforced in service layer
        ];
    }
}
