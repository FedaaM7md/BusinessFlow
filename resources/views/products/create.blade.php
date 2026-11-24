{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('products.new'))
@section('content')
    <x-page-header :title="__('products.new')" :subtitle="__('products.subtitle')"><x-slot:actions><a href="{{ route('products.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions></x-page-header>
    <form method="POST" action="{{ route('products.store') }}">@csrf @include('products.form')</form>
@endsection
