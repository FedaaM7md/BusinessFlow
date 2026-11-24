{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('users.title'))
@section('content')
    <x-page-header :title="__('users.title')" :subtitle="__('users.subtitle')">
        <x-slot:actions>@can('users.manage')<a href="{{ route('users.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('users.create') }}</a>@endcan</x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_180px_170px_auto]">
            <div class="relative"><span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span><input type="search" name="q" value="{{ request('q') }}" class="input ps-10" placeholder="{{ __('users.search_placeholder') }}"></div>
            <select name="role" class="select"><option value="">{{ __('users.role_filter') }}</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((string) request('role') === (string) $role->id)>{{ $role->displayName() }}</option>@endforeach</select>
            <select name="status" class="select"><option value="">{{ __('users.status_filter') }}</option><option value="active" @selected(request('status') === 'active')>{{ __('common.active') }}</option><option value="inactive" @selected(request('status') === 'inactive')>{{ __('common.inactive') }}</option></select>
            <div class="flex gap-2"><button class="btn-primary">{{ __('common.apply') }}</button><a href="{{ route('users.index') }}" class="btn-ghost">{{ __('common.reset') }}</a></div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($users as $user)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-800 text-xs font-bold text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                            <div class="min-w-0">
                                <p class="break-words text-sm font-semibold text-brand-900">{{ $user->name }}</p>
                                <p class="truncate text-[11px] text-brand-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="badge {{ $user->is_active ? 'bg-positive-50 text-positive-700 ring-positive-600/20' : 'bg-brand-50 text-brand-500 ring-brand-500/10' }}">{{ $user->is_active ? __('common.active') : __('common.inactive') }}</span>
                    </div>
                    <p class="mt-2 text-xs text-brand-500">{{ $user->role?->displayName() ?? '—' }}</p>
                    <div class="mt-3 flex items-center justify-end gap-1">
                        @can('users.manage')
                            <a href="{{ route('users.edit', $user) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')<button type="submit" class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button></form>
                            @endif
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state icon="users" :title="__('common.no_results')" :description="__('users.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><x-sortable-th column="name" :sort="$sort" :direction="$direction">{{ __('fields.name') }}</x-sortable-th><th>{{ __('users.role') }}</th><th>{{ __('fields.locale') }}</th><x-sortable-th column="last_login_at" :sort="$sort" :direction="$direction">{{ __('users.last_login') }}</x-sortable-th><th>{{ __('common.status') }}</th><th class="text-end">{{ __('common.actions') }}</th></tr></thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><div class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-800">{{ mb_substr($user->name, 0, 1) }}</span><div><p class="font-semibold text-slate-800">{{ $user->name }}</p><p class="text-[11px] text-slate-400">{{ $user->email }}</p></div></div></td>
                        <td><span class="badge bg-slate-100 text-slate-600 ring-slate-500/10">{{ $user->role?->displayName() ?? '—' }}</span></td>
                        <td class="text-slate-500">{{ $user->locale === 'ar' ? __('settings.language_arabic') : __('settings.language_english') }}</td>
                        <td class="text-slate-500">{{ $user->last_login_at?->format('Y-m-d H:i') ?? __('users.never_logged_in') }}</td>
                        <td><span class="badge {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">{{ $user->is_active ? __('common.active') : __('common.inactive') }}</span></td>
                        <td><div class="flex justify-end gap-1">
                            @can('users.manage')
                                <a href="{{ route('users.edit', $user) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')<button class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button></form>
                                @endif
                            @endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="users" :title="__('common.no_results')" :description="__('users.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$users" />
    </div>
@endsection
