<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralCompanionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('assign-referral-companions') ?? false;
    }

    public function rules(): array
    {
        return [
            'name_snapshot' => ['required', 'string', 'max:200'],
            'role_relationship' => ['required', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['sometimes', 'boolean'],
            'user_id' => ['nullable', 'string', 'exists:users,id'],
            // Forbidden: assigned_by_id, assigned_at, status (all server-authoritative)
        ];
    }
}
