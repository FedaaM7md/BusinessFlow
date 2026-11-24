{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('quotations.edit'))
@section('content')
    <x-page-header :title="__('quotations.edit')" :subtitle="$quotation->number">
        <x-slot:actions><a href="{{ route('quotations.show', $quotation) }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ route('quotations.update', $quotation) }}">
        @csrf @method('PUT')
        @include('quotations.form', ['quotation' => $quotation])
    </form>
@endsection
