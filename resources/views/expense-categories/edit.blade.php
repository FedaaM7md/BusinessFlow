{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('expenses.edit_category'))
@section('content')
    <x-page-header :title="__('expenses.edit_category')" :subtitle="$expenseCategory->name">
        <x-slot:actions><a href="{{ route('expense-categories.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ route('expense-categories.update', $expenseCategory) }}">
        @csrf @method('PUT')
        <div class="form-section">
            @include('expense-categories.form', ['expenseCategory' => $expenseCategory])
            <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5"><a href="{{ route('expense-categories.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a><button class="btn-primary">{{ __('common.save_changes') }}</button></div>
        </div>
    </form>
@endsection
