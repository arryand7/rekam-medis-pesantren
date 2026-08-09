<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRestrictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activity_status' => ['required', 'string', 'in:full_activity,limited_activity,rest,temporarily_not_cleared,other'],
            'effective_start' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'restriction_type' => ['required', 'string', 'in:bed_rest,light_duty_only,no_sports,no_heavy_lifting,dietary_restriction,isolation_rest,other'],
            'restriction_notes' => ['required', 'string', 'min:5', 'max:2000'],
            'allowed_activity_notes' => ['nullable', 'string', 'max:2000'],
            'review_date' => ['nullable', 'date'],
        ];
    }
}
