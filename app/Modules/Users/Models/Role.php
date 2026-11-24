<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Users\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'name_ar', 'name_en', 'permissions', 'is_system'];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' ? ($this->name_ar ?: $this->name_en) : ($this->name_en ?: $this->name_ar);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->name === 'admin') {
            return true;
        }

        return in_array($permission, $this->permissions ?? [], true);
    }

    /** @return array<string, array{label: string, permissions: array<int, string>}> */
    public static function permissionGroups(): array
    {
        return config('permissions.groups', []);
    }

    public static function allPermissionKeys(): array
    {
        return collect(static::permissionGroups())
            ->pluck('permissions')
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }
}
