<?php

namespace App\Http\Requests\Discharge;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationalHandoffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_type' => ['required', 'string', 'in:dorm_supervisor,homeroom_teacher,guardian,patient,staff_supervisor,other'],
            'recipient_reference' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'in:dorm_care_instruction,class_absence_notice,guardian_health_update,work_duty_modification,other'],
            'special_instructions' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
