<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Users\Services;

use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Users\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function updatePermissions(Role $role, array $permissions): Role
    {
        return DB::transaction(function () use ($role, $permissions) {
            if ($role->name === 'admin') {
                throw ValidationException::withMessages(['permissions' => __('messages.admin_permissions_locked')]);
            }

            $old = $role->permissions ?? [];
            $permissions = array_values(array_unique(array_intersect($permissions, Role::allPermissionKeys())));
            $role->update(['permissions' => $permissions]);

            $this->activity->log('roles', 'permissions_updated', 'Permissions updated for role '.$role->name, $role, [
                'permissions' => ['from' => $old, 'to' => $permissions],
            ]);

            return $role->refresh();
        });
    }
}
