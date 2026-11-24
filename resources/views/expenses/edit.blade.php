{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('expenses.edit'))
@section('content')
    <x-page-header :title="__('expenses.edit')" :subtitle="$expense->number">
        <x-slot:actions><a href="{{ route('expenses.show', $expense) }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ route('expenses.update', $expense) }}">
        @csrf @method('PUT')
        <div class="form-section">
            @include('expenses.form', ['expense' => $expense])
            <div class="mt-6 flex items-center justify-between gap-2 border-t border-slate-100 pt-5">
                <button type="button" class="btn-ghost !text-rose-600" data-submit-form="delete-expense" data-confirm="{{ __('common.confirm_delete_text') }}">{{ __('common.delete') }}</button>
                <div class="flex gap-2"><a href="{{ route('expenses.show', $expense) }}" class="btn-secondary">{{ __('common.cancel') }}</a><button class="btn-primary">{{ __('common.save_changes') }}</button></div>
            </div>
        </div>
    </form>
    <form id="delete-expense" method="POST" action="{{ route('expenses.destroy', $expense) }}" class="hidden">@csrf @method('DELETE')</form>
@endsection
