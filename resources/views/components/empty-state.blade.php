{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['icon' => 'document', 'title' => null, 'description' => null])
<div class="table-empty">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg border border-grid bg-brand-50 text-brand-400">
        <x-icon :name="$icon" class="h-7 w-7" />
    </div>
    <h3 class="mt-4 text-sm font-bold text-brand-900">{{ $title ?? __('common.no_results') }}</h3>
    <p class="mx-auto mt-1 max-w-md text-sm text-brand-500">{{ $description ?? __('common.no_results_hint') }}</p>
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
