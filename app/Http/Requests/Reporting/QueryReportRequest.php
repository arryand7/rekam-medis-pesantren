<?php

namespace App\Http\Requests\Reporting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QueryReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('view-health-reports') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_type' => ['required', 'string', 'in:visit_census,observation_census,referral_census,discharge_followup,pharmacy_stock,integration_delivery'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:50'],
            'destination' => ['nullable', 'string', 'max:50'],
            'is_low_stock' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}
