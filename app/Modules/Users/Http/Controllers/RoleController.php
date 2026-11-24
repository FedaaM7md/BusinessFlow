<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Http\Requests\RoleRequest;
use App\Modules\Users\Models\Role;
use App\Modules\Users\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $service) {}

    public function index(): View
    {
        $this->authorizePermission('roles.manage');
        $roles = Role::withCount('users')->orderBy('name')->get();
        $groups = Role::permissionGroups();

        return view('roles.index', compact('roles', 'groups'));
    }

    public function edit(Role $role): View
    {
        $this->authorizePermission('roles.manage');
        $groups = Role::permissionGroups();

        return view('roles.edit', compact('role', 'groups'));
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorizePermission('roles.manage');
        $this->service->updatePermissions($role, $request->validated('permissions') ?? []);

        return redirect()->route('roles.index')->with('success', __('messages.updated'));
    }
}
