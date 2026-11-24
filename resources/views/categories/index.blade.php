{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('products.categories_title'))
@section('content')
    <x-page-header :title="__('products.categories_title')" :subtitle="__('products.categories_subtitle')">
        <x-slot:actions>@can('products.manage')<a href="{{ route('categories.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('products.new_category') }}</a>@endcan</x-slot:actions>
    </x-page-header>
    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($categories as $category)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="break-words text-sm font-semibold text-brand-900">{{ $category->displayName() }}</p>
                        <span class="badge bg-brand-50 text-brand-600 ring-brand-500/10">{{ $category->products_count }} {{ __('products.category_products') }}</span>
                    </div>
                    @if($category->description)<p class="mt-1 text-xs text-brand-500">{{ $category->description }}</p>@endif
                    <div class="mt-3 flex items-center justify-end gap-1">
                        @can('products.manage')
                            <a href="{{ route('categories.edit', $category) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')<button type="submit" class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button></form>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state icon="categories" :title="__('products.no_categories')" :description="__('products.categories_subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('fields.name') }}</th><th>{{ __('products.category_products') }}</th><th class="text-end">{{ __('common.actions') }}</th></tr></thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><p class="font-semibold text-slate-800">{{ $category->displayName() }}</p>@if($category->description)<p class="max-w-xl truncate text-[11px] text-slate-400">{{ $category->description }}</p>@endif</td>
                            <td class="text-slate-500">{{ $category->products_count }}</td>
                            <td><div class="flex justify-end gap-1">
                                @can('products.manage')
                                    <a href="{{ route('categories.edit', $category) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')<button class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button></form>
                                @endcan
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state icon="categories" :title="__('products.no_categories')" :description="__('products.categories_subtitle')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <x-pagination :paginator="$categories" />
    </div>
@endsection
