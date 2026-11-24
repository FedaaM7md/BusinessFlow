{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['status'])
<span {{ $attributes->merge(['class' => 'badge '.status_classes($status)]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ __('statuses.'.$status) !== 'statuses.'.$status ? __('statuses.'.$status) : ucfirst(str_replace('_', ' ', $status)) }}
</span>
