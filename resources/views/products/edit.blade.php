{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('products.edit'))
@section('content')
    <x-page-header :title="__('products.edit')" :subtitle="$product->displayName()"><x-slot:actions><a href="{{ route('products.show', $product) }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions></x-page-header>
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf @method('PUT')
        @include('products.form', ['product' => $product])
        <div class="mt-5 flex items-center justify-between">
            <button type="button" class="btn-ghost !text-rose-600" data-submit-form="delete-product" data-confirm="{{ __('common.confirm_delete_text') }}">{{ __('common.delete') }}</button>
            <button class="btn-primary">{{ __('common.save_changes') }}</button>
        </div>
    </form>
    <form id="delete-product" method="POST" action="{{ route('products.destroy', $product) }}" class="hidden">@csrf @method('DELETE')</form>
@endsection
