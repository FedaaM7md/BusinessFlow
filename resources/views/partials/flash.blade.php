{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@if(session('success'))
    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 rounded-md border border-positive-600/30 bg-positive-50 px-4 py-3 text-sm text-positive-700" role="status">
        <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-positive-600" />
        <p class="flex-1">{{ session('success') }}</p>
        <button type="button" @click="show = false" class="rounded p-1 hover:bg-positive-100" aria-label="{{ __('common.close') }}"><x-icon name="close" class="h-4 w-4" /></button>
    </div>
@endif

@if(session('error'))
    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 rounded-md border border-negative-600/30 bg-negative-50 px-4 py-3 text-sm text-negative-700" role="alert">
        <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-negative-600" />
        <p class="flex-1">{{ session('error') }}</p>
        <button type="button" @click="show = false" class="rounded p-1 hover:bg-negative-100" aria-label="{{ __('common.close') }}"><x-icon name="close" class="h-4 w-4" /></button>
    </div>
@endif

@if($errors->any())
    <div class="mb-5 flex items-start gap-3 rounded-md border border-negative-600/30 bg-negative-50 px-4 py-3 text-sm text-negative-700" role="alert">
        <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-negative-600" />
        <div class="flex-1">
            <p class="font-bold">{{ __('common.validation_error') }}</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
