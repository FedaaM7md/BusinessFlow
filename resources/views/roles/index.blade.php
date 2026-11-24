{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('users.roles_title'))
@section('content')
    <x-page-header :title="__('users.roles_title')" :subtitle="__('users.roles_subtitle')" />
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        @foreach($roles as $role)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $role->displayName() }}</h2>
                        <p class="mt-1 text-xs text-slate-400">{{ $role->users_count }} {{ __('users.members') }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-800"><x-icon name="roles" class="h-5 w-5" /></span>
                </div>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="badge {{ $role->name === 'admin' ? 'bg-brand-50 text-brand-800 ring-brand-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/10' }}">{{ $role->name === 'admin' ? __('users.all_permissions') : __('users.permissions_count', ['count' => count($role->permissions ?? [])]) }}</span>
                    @if($role->name === 'admin')
                        <span class="text-xs text-slate-400">{{ __('users.admin_locked') }}</span>
                    @else
                        <a href="{{ route('roles.edit', $role) }}" class="btn-secondary !px-3 !py-1.5 text-xs">{{ __('users.edit_role') }}</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
