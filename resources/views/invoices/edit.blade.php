{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('invoices.edit'))
@section('content')
    <x-page-header :title="__('invoices.edit')" :subtitle="$invoice->number">
        <x-slot:actions><a href="{{ route('invoices.show', $invoice) }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf @method('PUT')
        @include('invoices.form', ['invoice' => $invoice])
    </form>
@endsection
