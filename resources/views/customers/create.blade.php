{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')

@section('title', __('customers.new'))

@section('content')
    <x-page-header :title="__('customers.new')" :subtitle="__('customers.subtitle')">
        <x-slot:actions><a href="{{ route('customers.index') }}" class="btn-secondary"><x-icon name="arrow-start" class="h-4 w-4 rtl:rotate-180" /> {{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        <div class="form-section">
            @include('customers.form')
            <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-5">
                <a href="{{ route('customers.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="btn-primary">{{ __('common.save') }}</button>
            </div>
        </div>
    </form>
@endsection
