<?php

namespace App\Http\Requests\Admin;

use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || ! $user->hasPermission('manage-roles')) {
            return false;
        }

        // If non-super-admin tries to assign protected permissions
        if (! $user->isSuperAdmin() && ! empty($this->input('permissions'))) {
            $requestedPerms = (array) $this->input('permissions');
            foreach ($requestedPerms as $permName) {
                if (Permission::isNameProtected($permName)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[a-z0-9_-]+$/', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'min:2', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Identifier role hanya boleh memuat huruf kecil, angka, garis bawah (_), dan tanda hubung (-).',
            'name.unique' => 'Nama identitas role ini sudah digunakan.',
            'display_name.required' => 'Nama tampilan role wajib diisi.',
        ];
    }
}
