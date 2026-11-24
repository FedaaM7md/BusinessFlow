{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('activity.title'))
@section('content')
    <x-page-header :title="__('activity.title')" :subtitle="__('activity.subtitle')" />

    <x-filter-panel>
        <form method="GET" action="{{ route('activity.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[180px_180px_150px_150px_auto]">
            <select name="user" class="select"><option value="">{{ __('activity.all_users') }}</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) request('user') === (string) $user->id)>{{ $user->name }}</option>@endforeach</select>
            <select name="module" class="select"><option value="">{{ __('activity.all_modules') }}</option>@foreach($modules as $module)<option value="{{ $module }}" @selected(request('module') === $module)>{{ __('activity.modules.'.$module) }}</option>@endforeach</select>
            <x-date-input name="from" :value="request('from')" />
            <x-date-input name="to" :value="request('to')" />
            <div class="flex gap-2"><button class="btn-primary">{{ __('common.apply') }}</button><a href="{{ route('activity.index') }}" class="btn-ghost">{{ __('common.reset') }}</a></div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($logs as $log)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-brand-800">{{ $log->user?->name ?? __('activity.system') }}</p>
                            <p class="truncate text-[11px] text-brand-400">{{ $log->user?->email }}</p>
                        </div>
                        <span class="badge bg-brand-50 text-brand-600 ring-brand-500/10">{{ __('activity.modules.'.$log->module) }}</span>
                    </div>
                    <p class="mt-2 break-words text-sm font-semibold text-brand-700">{{ __('activity.actions.'.$log->action) }}</p>
                    <p class="amount mt-2 text-[11px] text-brand-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            @empty
                <x-empty-state icon="activity" :title="__('activity.empty')" :description="__('activity.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>{{ __('activity.when') }}</th><th>{{ __('activity.user') }}</th><th>{{ __('activity.module') }}</th><th>{{ __('activity.description') }}</th><th>{{ __('activity.ip') }}</th></tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap text-xs text-slate-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td><p class="text-sm font-semibold text-slate-700">{{ $log->user?->name ?? __('activity.system') }}</p><p class="text-[11px] text-slate-400">{{ $log->user?->email }}</p></td>
                        <td><span class="badge bg-slate-100 text-slate-600 ring-slate-500/10">{{ __('activity.modules.'.$log->module) }}</span><p class="mt-1 text-[10px] text-slate-400">{{ __('activity.actions.'.$log->action) }}</p></td>
                        <td class="max-w-md">
                            <p class="text-sm font-semibold text-slate-700">{{ __('activity.actions.'.$log->action) }}</p>
                            <details class="mt-1">
                                <summary class="cursor-pointer text-[11px] font-semibold text-brand-800">{{ __('activity.details') }}</summary>
                                <div class="mt-2 space-y-1 rounded-lg bg-slate-50 p-2 text-[11px] text-slate-600">
                                    <p dir="ltr" class="text-slate-400">{{ $log->description }}</p>
                                    @if($log->changes)
                                        @foreach($log->changes as $field => $change)
                                            <p><span class="font-mono font-semibold">{{ $field }}</span>:
                                                <span class="text-rose-500">{{ is_array($change['from'] ?? null) ? json_encode($change['from'], JSON_UNESCAPED_UNICODE) : ($change['from'] ?? '—') }}</span>
                                                ← <span class="text-emerald-600">{{ is_array($change['to'] ?? null) ? json_encode($change['to'], JSON_UNESCAPED_UNICODE) : ($change['to'] ?? '—') }}</span>
                                            </p>
                                        @endforeach
                                    @endif
                                </div>
                            </details>
                        </td>
                        <td class="font-mono text-[11px] text-slate-400">{{ $log->ip_address ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="activity" :title="__('activity.empty')" :description="__('activity.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$logs" />
    </div>
@endsection
