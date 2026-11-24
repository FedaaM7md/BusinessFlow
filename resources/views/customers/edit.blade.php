{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')

@section('title', __('customers.edit'))

@section('content')
    <x-page-header :title="__('customers.edit')" :subtitle="$customer->name">
        <x-slot:actions><a href="{{ route('customers.show', $customer) }}" class="btn-secondary"><x-icon name="arrow-start" class="h-4 w-4 rtl:rotate-180" /> {{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf @method('PUT')
        <div class="form-section">
            @include('customers.form', ['customer' => $customer])
            <div class="mt-6 flex items-center justify-between gap-2 border-t border-slate-100 pt-5">
                <button type="button" class="btn-ghost !text-rose-600" data-submit-form="delete-customer" data-confirm="{{ __('common.confirm_delete_text') }}">{{ __('common.delete') }}</button>
                <div class="flex items-center gap-2">
                    <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                    <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
                </div>
            </div>
        </div>
    </form>
    <form id="delete-customer" method="POST" action="{{ route('customers.destroy', $customer) }}" class="hidden">@csrf @method('DELETE')</form>
@endsection
