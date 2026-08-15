<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUlids, Notifiable;

    protected $fillable = [
        'person_id',
        'name',
        'email',
        'password',
        'is_active',
        'theme_preference',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    /**
     * The roles that belong to the user.
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

    /**
     * Direct permissions assigned exceptionally to the user.
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'model_has_permissions', 'model_id', 'permission_id');
    }

    /**
     * Check if user has super admin authority.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user has a specific role by name or slug.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Check if user has a specific permission by name.
     */
    public function hasPermission(string $permissionName): bool
    {
        // 1. Super Admin bypass
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 2. Direct user permission (exceptional override)
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // 3. Role-based permissions
        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('name', $permissionName))
            ->exists();
    }

    /**
     * Get all effective permissions for this user with their exact grant source.
     *
     * @return array<string, array{id: string, name: string, display_name: string, source: string, is_direct: bool, is_protected: bool}>
     */
    public function getEffectivePermissionsWithSource(): array
    {
        if ($this->isSuperAdmin()) {
            return Permission::orderBy('name')->get()->mapWithKeys(function ($perm) {
                return [$perm->name => [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'display_name' => $perm->display_name,
                    'source' => 'SUPER ADMIN BYPASS',
                    'is_direct' => false,
                    'is_protected' => $perm->isProtected(),
                ]];
            })->toArray();
        }

        $effective = [];

        // Role-derived permissions
        $roles = $this->roles()->with('permissions')->get();
        foreach ($roles as $role) {
            $roleLabel = 'ROLE: '.($role->display_name ?? $role->name);
            foreach ($role->permissions as $perm) {
                if (! isset($effective[$perm->name])) {
                    $effective[$perm->name] = [
                        'id' => $perm->id,
                        'name' => $perm->name,
                        'display_name' => $perm->display_name,
                        'source' => $roleLabel,
                        'is_direct' => false,
                        'is_protected' => $perm->isProtected(),
                    ];
                } else {
                    $effective[$perm->name]['source'] .= ', '.$roleLabel;
                }
            }
        }

        // Direct user permissions (exceptional overrides)
        $directPerms = $this->permissions()->get();
        foreach ($directPerms as $perm) {
            if (! isset($effective[$perm->name])) {
                $effective[$perm->name] = [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'display_name' => $perm->display_name,
                    'source' => 'DIRECT USER',
                    'is_direct' => true,
                    'is_protected' => $perm->isProtected(),
                ];
            } else {
                $effective[$perm->name]['source'] .= ', DIRECT USER';
                $effective[$perm->name]['is_direct'] = true;
            }
        }

        ksort($effective);

        return $effective;
    }
}
