<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralStatusEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('record-destination-status') ?? false;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', 'in:arrived,accepted,declined,under_external_care,return_planned,returned'],
            'occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
            'contact_attribution' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'idempotency_key' => ['nullable', 'string', 'max:200'],
            // Forbidden: recorded_by_id, received_at, referral_id (all server-authoritative or route-bound)
        ];
    }
}
