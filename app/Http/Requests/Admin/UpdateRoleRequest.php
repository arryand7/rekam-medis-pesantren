<?php

namespace App\Http\Requests\Admin;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || ! $user->hasPermission('manage-roles')) {
            return false;
        }

        $roleId = $this->route('role') ?? $this->route('id');
        $role = $roleId instanceof Role ? $roleId : Role::find($roleId);

        if (! $role) {
            return false;
        }

        // Delegated admins cannot modify protected core roles.
        if ($role->isProtected() && ! $user->isSuperAdmin()) {
            return false;
        }

        // Delegated admins cannot add or strip protected permissions.
        if (! $user->isSuperAdmin()) {
            $requestedPerms = (array) $this->input('permissions', []);
            $currentPerms = $role->permissions->pluck('name')->toArray();

            foreach (Permission::PROTECTED_PERMISSIONS as $protectedPermission) {
                $wasAssigned = in_array($protectedPermission, $currentPerms, true);
                $willBeAssigned = in_array($protectedPermission, $requestedPerms, true);

                if ($wasAssigned !== $willBeAssigned) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $roleId = $this->route('role') ?? $this->route('id');
        $role = $roleId instanceof Role ? $roleId : Role::find($roleId);

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'display_name' => ['required', 'string', 'min:2', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
