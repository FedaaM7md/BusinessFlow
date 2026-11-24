<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Users\Services;

use App\Models\User;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Users\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(array $data, User $actor): User
    {
        if ($actor->is_demo && $this->roleIsAdmin($data['role_id'] ?? null)) {
            throw ValidationException::withMessages(['role_id' => __('messages.demo_cannot_create_admin')]);
        }

        return DB::transaction(function () use ($data) {
            $user = User::create($data);
            $this->activity->log('users', 'created', 'User '.$user->email.' created', $user, [
                'role_id' => ['from' => null, 'to' => $user->role_id],
            ]);

            return $user;
        });
    }

    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor) {
            if (! $data['is_active'] && $user->id === $actor->id) {
                throw ValidationException::withMessages(['is_active' => __('messages.cannot_deactivate_self')]);
            }

            if (! $data['is_active'] && $user->isAdmin() && User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->where('is_active', true)->count() <= 1) {
                throw ValidationException::withMessages(['is_active' => __('messages.last_admin')]);
            }

            // Protect demo accounts: identity, secrets and availability are fixed.
            if ($user->is_demo) {
                $demoErrors = [];

                if (array_key_exists('email', $data) && $data['email'] !== $user->email) {
                    $demoErrors['email'] = __('messages.demo_email_locked');
                }

                if (! empty($data['password'])) {
                    $demoErrors['password'] = __('messages.demo_password_locked');
                }

                if (! $data['is_active']) {
                    $demoErrors['is_active'] = __('messages.demo_cannot_deactivate');
                }

                if ($demoErrors !== []) {
                    throw ValidationException::withMessages($demoErrors);
                }
            }

            // Demo users may not create or promote administrators.
            if ($actor->is_demo && array_key_exists('role_id', $data) && $this->roleIsAdmin($data['role_id']) && (int) $user->role_id !== (int) $data['role_id']) {
                throw ValidationException::withMessages(['role_id' => __('messages.demo_cannot_create_admin')]);
            }

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $user->fill($data)->save();
            $changes = $this->activity->changedFields($user);
            if ($changes !== []) {
                $this->activity->log('users', 'updated', 'User '.$user->email.' updated', $user, $changes);
            }

            return $user->refresh();
        });
    }

    public function delete(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            if ($user->id === $actor->id) {
                throw ValidationException::withMessages(['delete' => __('messages.cannot_delete_self')]);
            }

            if ($user->is_demo) {
                throw ValidationException::withMessages(['delete' => __('messages.demo_cannot_delete')]);
            }

            if ($user->isAdmin() && User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->count() <= 1) {
                throw ValidationException::withMessages(['delete' => __('messages.last_admin')]);
            }

            $email = $user->email;
            $user->delete();
            $this->activity->log('users', 'deleted', 'User '.$email.' deleted', $user);
        });
    }

    private function roleIsAdmin(?int $roleId): bool
    {
        if (! $roleId) {
            return false;
        }

        return Role::query()->whereKey($roleId)->where('name', 'admin')->exists();
    }
}
