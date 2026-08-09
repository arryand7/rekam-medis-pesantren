<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'follow_up_type' => ['required', 'string', 'in:poskestren_recheck,external_facility,activity_reassessment,medication_review,wound_review,other'],
            'due_at' => ['nullable', 'date'],
            'healthcare_partner_id' => ['nullable', 'exists:healthcare_partners,id'],
            'instructions' => ['required', 'string', 'min:5', 'max:2000'],
            'responsible_party_type' => ['nullable', 'string', 'in:dorm_supervisor,guardian,poskestren_staff,patient,other'],
            'responsible_party_reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
