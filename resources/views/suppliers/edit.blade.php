{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('suppliers.edit'))
@section('content')
    <x-page-header :title="__('suppliers.edit')" :subtitle="$supplier->name">
        <x-slot:actions><a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf @method('PUT')
        <div class="form-section">
            @include('suppliers.form', ['supplier' => $supplier])
            <div class="mt-6 flex items-center justify-between gap-2 border-t border-slate-100 pt-5">
                <button type="button" class="btn-ghost !text-rose-600" data-submit-form="delete-supplier" data-confirm="{{ __('common.confirm_delete_text') }}">{{ __('common.delete') }}</button>
                <div class="flex gap-2">
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                    <button class="btn-primary">{{ __('common.save_changes') }}</button>
                </div>
            </div>
        </div>
    </form>
    <form id="delete-supplier" method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="hidden">@csrf @method('DELETE')</form>
@endsection
