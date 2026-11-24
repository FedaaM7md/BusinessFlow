{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('users.edit_role'))
@section('content')
    <x-page-header :title="__('users.edit_role')" :subtitle="$role->displayName()">
        <x-slot:actions><a href="{{ route('roles.index') }}" class="btn-secondary">{{ __('roles.back_to_roles') }}</a></x-slot:actions>
    </x-page-header>

    @if($role->name === 'admin')
        <div class="mb-5 rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-900">{{ __('users.admin_locked') }}</div>
    @endif

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf @method('PUT')
        <div class="card p-5 sm:p-6">
            <div>
                <h2 class="section-title">{{ __('users.permission_matrix') }}</h2>
                <p class="muted mt-1">{{ __('users.permission_matrix_hint') }}</p>
            </div>
            <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                @foreach($groups as $groupKey => $group)
                    <div class="rounded-xl border border-slate-200">
                        <div class="border-b border-slate-100 bg-slate-50 px-4 py-3">
                            <p class="text-sm font-bold text-slate-800">{{ __($group['label']) }}</p>
                        </div>
                        <div class="space-y-3 p-4">
                            @foreach($group['permissions'] as $permission)
                                <label class="flex items-start gap-3">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', $role->permissions ?? []), true)) @disabled($role->name === 'admin') class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-800 focus:ring-brand-500">
                                    <span class="text-sm text-slate-600">{{ __('permissions.items.'.$permission) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions')<p class="error-text mt-3">{{ $message }}</p>@enderror
            <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                <a href="{{ route('roles.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                @if($role->name !== 'admin')<button class="btn-primary">{{ __('roles.save_matrix') }}</button>@endif
            </div>
        </div>
    </form>
@endsection
