{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('suppliers.new'))
@section('content')
    <x-page-header :title="__('suppliers.new')" :subtitle="__('suppliers.subtitle')">
        <x-slot:actions><a href="{{ route('suppliers.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        <div class="form-section">
            @include('suppliers.form')
            <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                <a href="{{ route('suppliers.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                <button class="btn-primary">{{ __('common.save') }}</button>
            </div>
        </div>
    </form>
@endsection
