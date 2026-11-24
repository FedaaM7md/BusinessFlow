<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Providers;

use App\Models\User;
use App\Modules\Users\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach (Role::allPermissionKeys() as $permission) {
            Gate::define($permission, function (User $user) use ($permission) {
                return $user->isActive() && $user->hasPermission($permission);
            });
        }
    }
}
