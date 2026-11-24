<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Users\Http\Requests\UserRequest;
use App\Modules\Users\Models\Role;
use App\Modules\Users\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('users.view');
        $sort = $this->sortColumn($request->query('sort'), ['name', 'email', 'created_at', 'last_login_at']);
        $direction = $this->sortDirection($request->query('direction'));

        $users = User::query()
            ->with('role')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.$request->query('q').'%')
                ->orWhere('email', 'like', '%'.$request->query('q').'%')))
            ->when($request->filled('role'), fn ($q) => $q->where('role_id', $request->query('role')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->query('status') === 'active'))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizePermission('users.manage');
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorizePermission('users.manage');
        $this->service->create($request->validated(), $request->user());

        return redirect()->route('users.index')->with('success', __('messages.created'));
    }

    public function edit(User $user): View
    {
        $this->authorizePermission('users.manage');
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorizePermission('users.manage');
        $this->service->update($user, $request->validated(), $request->user());

        return redirect()->route('users.index')->with('success', __('messages.updated'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizePermission('users.manage');
        $this->service->delete($user, $request->user());

        return redirect()->route('users.index')->with('success', __('messages.deleted'));
    }
}
