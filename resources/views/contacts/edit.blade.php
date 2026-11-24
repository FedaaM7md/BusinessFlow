{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')

@section('title', __('customers.edit_contact'))

@section('content')
    <x-page-header :title="__('customers.edit_contact')" :subtitle="$customer->name">
        <x-slot:actions><a href="{{ route('customers.show', $customer) }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('contacts.update', [$customer, $contact]) }}">
        @csrf @method('PUT')
        <div class="form-section">
            @include('contacts.form', ['contact' => $contact])
            <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="btn-primary">{{ __('common.save_changes') }}</button>
            </div>
        </div>
    </form>
@endsection
