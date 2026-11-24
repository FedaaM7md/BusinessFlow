{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['id' => null, 'name', 'value' => '', 'required' => false, 'max' => null])
@php($fieldId = $id ?? $name)
<input
    id="{{ $fieldId }}"
    type="text"
    name="{{ $name }}"
    value="{{ $value }}"
    inputmode="numeric"
    pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
    placeholder="{{ __('common.date_placeholder') }}"
    title="{{ __('common.date_placeholder') }}"
    autocomplete="off"
    @if($required) required @endif
    @if($max) data-max="{{ $max }}" @endif
    {{ $attributes->merge(['class' => 'input']) }}
>
@error($name) <p class="error-text">{{ $message }}</p> @enderror
