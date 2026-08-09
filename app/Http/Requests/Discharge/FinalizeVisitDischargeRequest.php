<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class FinalizeVisitDischargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clinical_summary' => ['nullable', 'string', 'min:10', 'max:5000'],
            'final_condition' => ['nullable', 'string', 'max:255'],
            'activity_recommendation' => ['nullable', 'string', 'in:full_activity,limited_activity,rest,temporarily_not_cleared,other'],
            'rest_recommendation' => ['nullable', 'string', 'max:2000'],
            'restriction_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
