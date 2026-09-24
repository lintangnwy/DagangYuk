<?php

namespace App\Traits;

trait HasPermissions
{
    /**
     * Nama role user ini (super_admin | admin | user).
     */
    public function roleName(): ?string
    {
        return $this->role?->name;
    }

    /**
     * Daftar permission yang dimiliki user berdasarkan role-nya.
     */
    public function permissionsList(): array
    {
        $role = $this->roleName();

        if (! $role) {
            return [];
        }

        return config("permissions.roles.{$role}", []);
    }

    /**
     * Accessor — otomatis ikut ter-serialize ke JSON (lihat $appends di User).
     */
    public function getPermissionsAttribute(): array
    {
        return $this->permissionsList();
    }

    /**
     * Cek satu permission. Mendukung wildcard di config, mis. 'shop.*'.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissionsList();

        if (in_array($permission, $permissions, true)) {
            return true;
        }

        foreach ($permissions as $granted) {
            if (! str_ends_with($granted, '.*')) {
                continue;
            }

            $prefix = substr($granted, 0, -2);

            if ($permission === $prefix || str_starts_with($permission, $prefix . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * User dianggap punya akses jika memiliki salah satu permission yang diminta.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
