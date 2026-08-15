<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FilterInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('view-pharmacy-inventory') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('search')) {
            $this->merge(['search' => trim((string) $this->input('search'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'condition' => ['nullable', 'string', 'in:available,near_expiry,expired,depleted'],
            'location' => ['nullable', 'string', 'exists:stock_locations,id'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
