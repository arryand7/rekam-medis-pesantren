<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserDirectPermissionsRequest;
use App\Http\Requests\Admin\UpdateUserRolesRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-users');

        $query = User::with(['person', 'roles', 'permissions']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('person', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('nis_nip', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $roleFilter = $request->input('role');
            $query->whereHas('roles', fn ($q) => $q->where('name', $roleFilter));
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('pages.users.index', compact('users', 'roles'));
    }

    public function show(string $id): View
    {
        Gate::authorize('manage-users');

        $user = User::with(['person', 'roles.permissions', 'permissions'])->findOrFail($id);
        $effectivePermissions = $user->getEffectivePermissionsWithSource();

        $allRoles = Role::orderBy('name')->get();
        $groupedPermissions = Permission::getGroupedPermissions();
        $actor = auth()->user();
        $isActorSuperAdmin = $actor?->isSuperAdmin() ?? false;

        return view('pages.users.show', compact(
            'user',
            'effectivePermissions',
            'allRoles',
            'groupedPermissions',
            'isActorSuperAdmin'
        ));
    }

    public function updateRoles(UpdateUserRolesRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($id, $validated) {
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            $user->load('roles');

            $before = [
                'roles' => $user->roles->pluck('name')->toArray(),
            ];

            $requestedRoleIds = [];
            if (! empty($validated['roles'])) {
                $requestedRoleIds = Role::whereIn('id', $validated['roles'])
                    ->orWhereIn('name', $validated['roles'])
                    ->pluck('id')
                    ->toArray();
            }

            $requestedRoleNames = Role::whereIn('id', $requestedRoleIds)->pluck('name')->toArray();
            if (in_array('super_admin', $before['roles'], true) && ! in_array('super_admin', $requestedRoleNames, true)) {
                User::where('is_active', true)->lockForUpdate()->get(['id']);

                $activeSuperAdminCount = User::where('is_active', true)
                    ->whereHas('roles', fn ($query) => $query->where('name', 'super_admin'))
                    ->count();

                if ($activeSuperAdminCount <= 1) {
                    return null;
                }
            }

            $user->roles()->sync($requestedRoleIds);
            $user->load('roles');

            $after = [
                'roles' => $user->roles->pluck('name')->toArray(),
            ];

            AuditLogService::log(
                'USER_ROLE_ASSIGNED',
                User::class,
                $user->id,
                $before,
                $after,
                'Penugasan role untuk '.$user->name.' diperbarui oleh '.(auth()->user()->name ?? 'System')
            );

            return $user;
        });

        if ($user === null) {
            return redirect()->back()->with('error', 'Tidak dapat mencabut role dari akun Super Admin aktif terakhir di sistem.');
        }

        return redirect()->route('users.show', $user->id)->with('success', "Penugasan role untuk '{$user->name}' berhasil diperbarui.");
    }

    public function updateDirectPermissions(UpdateUserDirectPermissionsRequest $request, string $id): RedirectResponse
    {
        $user = User::with('permissions')->findOrFail($id);
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            $before = [
                'direct_permissions' => $user->permissions->pluck('name')->toArray(),
            ];

            $permIds = [];
            if (! empty($validated['permissions'])) {
                $permIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->toArray();
            }

            $user->permissions()->sync($permIds);
            $user->load('permissions');

            $after = [
                'direct_permissions' => $user->permissions->pluck('name')->toArray(),
            ];

            AuditLogService::log(
                'USER_PERMISSION_GRANTED',
                User::class,
                $user->id,
                $before,
                $after,
                'Hak akses langsung (exception) untuk '.$user->name.' diperbarui oleh '.(auth()->user()->name ?? 'System')
            );
        });

        return redirect()->route('users.show', $user->id)->with('success', "Hak akses langsung (Direct Permissions) untuk '{$user->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, string $id): RedirectResponse
    {
        Gate::authorize('manage-users');

        $result = DB::transaction(function () use ($id) {
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($user->is_active && $user->isSuperAdmin()) {
                $activeSuperAdminIds = User::where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
                    ->lockForUpdate()
                    ->pluck('id');

                if ($activeSuperAdminIds->count() <= 1) {
                    return null;
                }
            }

            $oldStatus = $user->is_active;
            $user->is_active = ! $oldStatus;
            $user->save();

            AuditLogService::log(
                'USER_STATUS_TOGGLED',
                User::class,
                $user->id,
                ['is_active' => $oldStatus],
                ['is_active' => $user->is_active],
                'Status akun '.$user->name.' diubah menjadi '.($user->is_active ? 'Aktif' : 'Non-Aktif').' oleh '.(auth()->user()->name ?? 'System')
            );

            return $user;
        });

        if ($result === null) {
            return redirect()->back()->with('error', 'Tidak dapat menonaktifkan akun Super Admin terakhir di sistem.');
        }

        $statusText = $result->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('users.show', $result->id)->with('success', "Akun '{$result->name}' telah berhasil {$statusText}.");
    }
}
