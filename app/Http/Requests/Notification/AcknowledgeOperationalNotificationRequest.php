<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AcknowledgeOperationalNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('acknowledge-operational-notifications') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'acknowledgement_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
