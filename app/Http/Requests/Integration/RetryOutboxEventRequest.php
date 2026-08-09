<?php

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

class RetryOutboxEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('retry-integration-events') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
