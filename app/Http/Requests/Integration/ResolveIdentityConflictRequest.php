<?php

namespace App\Http\Requests\Integration;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResolveIdentityConflictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('resolve-integration-conflicts') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resolution_notes' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'resolution_notes.required' => 'Catatan penyelesaian konflik wajib diisi.',
        ];
    }
}
