{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('users.new'))
@section('content')
    <x-page-header :title="__('users.new')" :subtitle="__('users.subtitle')"><x-slot:actions><a href="{{ route('users.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions></x-page-header>
    <form method="POST" action="{{ route('users.store') }}">@csrf<div class="form-section">@include('users.form')<div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5"><a href="{{ route('users.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a><button class="btn-primary">{{ __('common.save') }}</button></div></div></form>
@endsection
