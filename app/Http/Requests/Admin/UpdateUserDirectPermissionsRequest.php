<?php

namespace App\Http\Requests\Admin;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserDirectPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        if (! $actor || ! ($actor->hasPermission('manage-permissions') || $actor->hasPermission('manage-users'))) {
            return false;
        }

        $userId = $this->route('user') ?? $this->route('id');
        $targetUser = $userId instanceof User ? $userId : User::find($userId);

        if (! $targetUser) {
            return false;
        }

        // Self-escalation check: normal admin cannot modify their own direct permissions
        if (! $actor->isSuperAdmin() && $actor->id === $targetUser->id) {
            return false;
        }

        // Delegated admins cannot add or strip protected direct permissions.
        if (! $actor->isSuperAdmin()) {
            $requestedPerms = (array) $this->input('permissions', []);
            $currentDirectPerms = $targetUser->permissions->pluck('name')->toArray();

            foreach (Permission::PROTECTED_PERMISSIONS as $protectedPermission) {
                $wasAssigned = in_array($protectedPermission, $currentDirectPerms, true);
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
        return [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
