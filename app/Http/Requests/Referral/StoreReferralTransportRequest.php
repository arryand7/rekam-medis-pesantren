<?php

namespace App\Http\Requests\Referral;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralTransportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('arrange-referral-transport') ?? false;
    }

    public function rules(): array
    {
        return [
            'transport_type' => ['required', 'string', 'in:school_vehicle,ambulance_partner,external_ambulance,private_vehicle,other'],
            'vehicle_identifier' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:200'],
            'driver_contact' => ['nullable', 'string', 'max:50'],
            'departure_planned' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // Forbidden: arranged_by_id, arranged_at, status (all server-authoritative)
        ];
    }
}
