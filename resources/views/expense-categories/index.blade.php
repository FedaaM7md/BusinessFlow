{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('expenses.categories_title'))
@section('content')
    <x-page-header :title="__('expenses.categories_title')" :subtitle="__('expenses.categories_subtitle')">
        <x-slot:actions>@can('expenses.manage')<a href="{{ route('expense-categories.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('expenses.new_category') }}</a>@endcan</x-slot:actions>
    </x-page-header>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($categories as $category)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="break-words text-sm font-semibold text-brand-900">{{ $category->displayName() }}</p>
                            @if(app()->getLocale() === 'en' && $category->name_ar)<p class="mt-0.5 text-[11px] text-brand-400">{{ $category->name_ar }}</p>@endif
                        </div>
                        <span class="badge {{ $category->is_active ? 'bg-positive-50 text-positive-700 ring-positive-600/20' : 'bg-brand-50 text-brand-500 ring-brand-500/10' }}">{{ $category->is_active ? __('common.active') : __('common.inactive') }}</span>
                    </div>
                    <p class="mt-2 text-xs text-brand-500">{{ $category->expenses_count }} {{ __('expenses.expense_count') }}</p>
                    <div class="mt-3 flex items-center justify-end gap-1">
                        @can('expenses.manage')
                            <a href="{{ route('expense-categories.edit', $category) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                            <form method="POST" action="{{ route('expense-categories.destroy', $category) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')
                                <button type="submit" class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state icon="categories" :title="__('expenses.no_categories')" :description="__('expenses.categories_subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('expenses.category_name') }}</th><th>{{ __('expenses.expense_count') }}</th><th>{{ __('common.status') }}</th><th class="text-end">{{ __('common.actions') }}</th></tr></thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><p class="font-semibold text-slate-800">{{ $category->displayName() }}</p>@if(app()->getLocale() === 'en' && $category->name_ar)<p class="text-[11px] text-slate-400">{{ $category->name_ar }}</p>@endif</td>
                            <td class="text-slate-500">{{ $category->expenses_count }}</td>
                            <td><span class="badge {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">{{ $category->is_active ? __('common.active') : __('common.inactive') }}</span></td>
                            <td><div class="flex justify-end gap-1">
                                @can('expenses.manage')
                                    <a href="{{ route('expense-categories.edit', $category) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                    <form method="POST" action="{{ route('expense-categories.destroy', $category) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')
                                        <button class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                    </form>
                                @endcan
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state icon="categories" :title="__('expenses.no_categories')" :description="__('expenses.categories_subtitle')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <x-pagination :paginator="$categories" />
    </div>
@endsection
