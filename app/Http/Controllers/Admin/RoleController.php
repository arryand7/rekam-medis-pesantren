<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-roles');

        $query = Role::with(['permissions'])->withCount('users');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->orderBy('name')->get();
        $totalPermissions = Permission::count();

        return view('pages.roles.index', compact('roles', 'totalPermissions'));
    }

    public function create(): View
    {
        Gate::authorize('manage-roles');

        $groupedPermissions = Permission::getGroupedPermissions();
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;

        return view('pages.roles.create', compact('groupedPermissions', 'isSuperAdmin'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
            ]);

            if (! empty($validated['permissions'])) {
                $permIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->toArray();
                $role->permissions()->sync($permIds);
            }

            AuditLogService::log(
                'ROLE_CREATED',
                Role::class,
                $role->id,
                null,
                [
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'permissions' => $validated['permissions'] ?? [],
                ],
                'Role baru dibuat oleh '.(auth()->user()->name ?? 'System')
            );

            return $role;
        });

        return redirect()->route('roles.show', $role->id)->with('success', "Role '{$role->display_name}' berhasil dibuat.");
    }

    public function show(string $id): View
    {
        Gate::authorize('manage-roles');

        $role = Role::with(['permissions', 'users.person'])->withCount('users')->findOrFail($id);
        $groupedPermissions = Permission::getGroupedPermissions();

        return view('pages.roles.show', compact('role', 'groupedPermissions'));
    }

    public function edit(string $id): View
    {
        Gate::authorize('manage-roles');

        $role = Role::with('permissions')->findOrFail($id);
        $groupedPermissions = Permission::getGroupedPermissions();
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
        $currentPermissions = $role->permissions->pluck('name')->toArray();

        return view('pages.roles.edit', compact('role', 'groupedPermissions', 'isSuperAdmin', 'currentPermissions'));
    }

    public function update(UpdateRoleRequest $request, string $id): RedirectResponse
    {
        $role = Role::with('permissions')->findOrFail($id);
        $validated = $request->validated();

        DB::transaction(function () use ($role, $validated) {
            $before = [
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];

            $updateData = [
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
            ];

            // Only allow updating internal identifier if not a protected core role
            if (! $role->isProtected()) {
                $updateData['name'] = $validated['name'];
            }

            $role->update($updateData);

            if (isset($validated['permissions'])) {
                $permIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->toArray();
                $role->permissions()->sync($permIds);
            }

            $role->load('permissions');
            $after = [
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];

            AuditLogService::log(
                'ROLE_UPDATED',
                Role::class,
                $role->id,
                $before,
                $after,
                'Role '.$role->display_name.' diperbarui oleh '.(auth()->user()->name ?? 'System')
            );
        });

        return redirect()->route('roles.show', $role->id)->with('success', "Perubahan pada role '{$role->display_name}' berhasil disimpan.");
    }

    public function destroy(string $id): RedirectResponse
    {
        Gate::authorize('manage-roles');

        $role = Role::withCount('users')->findOrFail($id);

        if ($role->isProtected()) {
            return redirect()->back()->with('error', "Role inti sistem '{$role->display_name}' tidak dapat dihapus demi keamanan aplikasi.");
        }

        if ($role->users_count > 0) {
            return redirect()->back()->with('error', "Role '{$role->display_name}' tidak dapat dihapus karena masih ditugaskan kepada {$role->users_count} pengguna.");
        }

        DB::transaction(function () use ($role) {
            $before = [
                'name' => $role->name,
                'display_name' => $role->display_name,
                'permissions' => $role->permissions()->pluck('name')->toArray(),
            ];

            $role->permissions()->detach();
            $role->delete();

            AuditLogService::log(
                'ROLE_DELETED',
                Role::class,
                $role->id,
                $before,
                null,
                'Role '.$role->display_name.' dihapus oleh '.(auth()->user()->name ?? 'System')
            );
        });

        return redirect()->route('roles.index')->with('success', "Role '{$role->display_name}' telah berhasil dihapus.");
    }
}
