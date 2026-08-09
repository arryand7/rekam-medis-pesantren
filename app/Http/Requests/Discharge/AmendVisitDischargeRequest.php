<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class AmendVisitDischargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amendment_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'discharge_type' => ['nullable', 'string', 'in:return_to_activity,rest_required,continue_poskestren_care,follow_up_external,referred_again,transfer_of_care,other'],
            'discharge_destination' => ['nullable', 'string', 'max:255'],
            'clinical_summary' => ['nullable', 'string', 'min:10', 'max:5000'],
            'final_condition' => ['nullable', 'string', 'max:255'],
            'activity_recommendation' => ['nullable', 'string', 'in:full_activity,limited_activity,rest,temporarily_not_cleared,other'],
            'rest_recommendation' => ['nullable', 'string', 'max:2000'],
            'restriction_notes' => ['nullable', 'string', 'max:2000'],
            'follow_up_required' => ['nullable', 'boolean'],
            'follow_up_summary' => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date'],
            'follow_up_partner_id' => ['nullable', 'exists:healthcare_partners,id'],
        ];
    }
}
