{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('invoices.new'))
@section('content')
    <x-page-header :title="__('invoices.new')" :subtitle="__('invoices.subtitle')">
        <x-slot:actions><a href="{{ route('invoices.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        @include('invoices.form')
    </form>
@endsection
