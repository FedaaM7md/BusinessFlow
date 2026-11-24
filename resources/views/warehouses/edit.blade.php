{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('inventory.edit_warehouse'))
@section('content')
    <x-page-header :title="__('inventory.edit_warehouse')" :subtitle="$warehouse->name"><x-slot:actions><a href="{{ route('warehouses.show', $warehouse) }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions></x-page-header>
    <form method="POST" action="{{ route('warehouses.update', $warehouse) }}">@csrf @method('PUT')<div class="form-section">@include('warehouses.form', ['warehouse' => $warehouse])<div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5"><a href="{{ route('warehouses.show', $warehouse) }}" class="btn-secondary">{{ __('common.cancel') }}</a><button class="btn-primary">{{ __('common.save_changes') }}</button></div></div></form>
@endsection
