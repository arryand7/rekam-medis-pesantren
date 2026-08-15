<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        if (! $actor || ! ($actor->hasPermission('manage-users') || $actor->hasPermission('manage-roles'))) {
            return false;
        }

        $userId = $this->route('user') ?? $this->route('id');
        $targetUser = $userId instanceof User ? $userId : User::find($userId);

        if (! $targetUser) {
            return false;
        }

        $requestedRoleIds = (array) $this->input('roles', []);
        $requestedRoleNames = Role::whereIn('id', $requestedRoleIds)->orWhereIn('name', $requestedRoleIds)->pluck('name')->toArray();
        $currentRoleNames = $targetUser->roles->pluck('name')->toArray();

        // 1. Prevent delegated admins from assigning or stripping protected roles.
        if (! $actor->isSuperAdmin()) {
            foreach (Role::PROTECTED_ROLES as $protectedRole) {
                $wasAssigned = in_array($protectedRole, $currentRoleNames, true);
                $willBeAssigned = in_array($protectedRole, $requestedRoleNames, true);

                if ($wasAssigned !== $willBeAssigned) {
                    return false;
                }
            }
        }

        // 2. Last Super Admin Protection
        if (in_array('super_admin', $currentRoleNames, true) && ! in_array('super_admin', $requestedRoleNames, true)) {
            $superAdminCount = User::where('is_active', true)
                ->whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
                ->count();

            if ($superAdminCount <= 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
        ];
    }
}
