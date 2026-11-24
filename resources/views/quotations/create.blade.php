{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('quotations.new'))
@section('content')
    <x-page-header :title="__('quotations.new')" :subtitle="__('quotations.subtitle')">
        <x-slot:actions><a href="{{ route('quotations.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ route('quotations.store') }}">
        @csrf
        @include('quotations.form')
    </form>
@endsection
