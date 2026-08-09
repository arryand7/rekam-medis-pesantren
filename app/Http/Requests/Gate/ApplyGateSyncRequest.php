<?php

namespace App\Http\Requests\Gate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyGateSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('execute-gate-sync-apply') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }
}
