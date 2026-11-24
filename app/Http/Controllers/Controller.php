<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    protected function authorizePermission(string $permission): void
    {
        Gate::authorize($permission);
    }

    /** Whitelist sortable columns to prevent SQL injection. */
    protected function sortColumn(?string $column, array $allowed, string $fallback = 'created_at'): string
    {
        return in_array($column, $allowed, true) ? $column : $fallback;
    }

    protected function sortDirection(?string $direction): string
    {
        return strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';
    }
}
