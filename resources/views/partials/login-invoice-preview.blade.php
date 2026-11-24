{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php
    $preview = $preview ?? null;
    $company = company();
@endphp

@if($preview)
    <div class="mx-auto w-full max-w-md -rotate-1 rounded-md border border-grid bg-white p-5 shadow-[0_24px_60px_-28px_rgba(20,33,61,0.45)]">
        <div class="flex items-start justify-between gap-3 border-b border-grid pb-3">
            <div class="flex items-center gap-2.5">
                <x-logo class="h-9 w-9" />
                <div>
                    <p class="text-xs font-bold text-brand-900" dir="auto">{{ $company->displayName() }}</p>
                    <p class="text-[10px] text-brand-400">{{ __('common.footer_tagline') }}</p>
                </div>
            </div>
            <div class="text-end">
                <p class="ledger-stamp inline-block text-[9px] text-brand-500">{{ __('invoices.print_title') }}</p>
                <p class="code mt-1.5 text-xs font-bold text-brand-900" dir="ltr">{{ $preview->number }}</p>
            </div>
        </div>

        <div class="mt-3 flex items-start justify-between gap-3 text-[10px]">
            <div>
                <p class="text-brand-400">{{ __('invoices.invoice_for') }}</p>
                <p class="mt-0.5 text-xs font-semibold text-brand-900" dir="auto">{{ $preview->customer?->name ?? __('common.not_available') }}</p>
            </div>
            <div class="text-end text-brand-400">
                <p>{{ __('invoices.issue_date') }}</p>
                <p class="code mt-0.5 text-[10px] text-brand-700" dir="ltr">{{ $preview->issue_date->format('Y-m-d') }}</p>
            </div>
        </div>

        <table class="mt-3 w-full text-[11px]">
            <thead>
                <tr class="border-b border-brand-800 text-brand-500">
                    <th class="py-1.5 text-start font-semibold">{{ __('fields.description') }}</th>
                    <th class="py-1.5 text-end font-semibold">{{ __('fields.quantity') }}</th>
                    <th class="py-1.5 text-end font-semibold">{{ __('fields.total') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($preview->lines->take(4) as $line)
                <tr class="border-b border-grid/70">
                    <td class="py-1.5 pe-2 text-brand-800" dir="auto">{{ \Illuminate\Support\Str::limit($line->description, 26) }}</td>
                    <td class="py-1.5 text-end amount text-brand-500">{{ quantity($line->quantity) }}</td>
                    <td class="py-1.5 text-end amount font-semibold text-brand-900">{{ money($line->total_minor) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-3 flex items-center justify-between border-t-2 border-brand-800 pt-2">
            <span class="text-xs font-bold text-brand-900">{{ __('fields.total') }}</span>
            <x-money :minor="$preview->total_minor" class="text-sm font-bold text-brand-900" />
        </div>

        <div class="mt-3 flex items-center justify-between text-[9px] text-brand-400">
            <span>{{ __('common.demo') }}</span>
            <span class="text-[9px] text-brand-400">{{ __('statuses.'.$preview->status) }}</span>
        </div>
    </div>
@else
    <div class="mx-auto w-full max-w-md rounded-md border border-grid bg-white p-7 shadow-[0_24px_60px_-28px_rgba(20,33,61,0.45)]">
        <div class="flex items-center gap-3 border-b border-grid pb-4">
            <x-logo class="h-11 w-11" />
            <div>
                <p class="text-sm font-bold text-brand-900">{{ __('common.app_name') }}</p>
                <p class="text-[11px] text-brand-400">{{ __('common.footer_tagline') }}</p>
            </div>
        </div>
        <p class="mt-5 text-lg font-bold leading-snug text-brand-900">{{ __('auth.hero_title') }}</p>
        <p class="mt-2 text-sm leading-6 text-brand-500">{{ __('auth.hero_subtitle') }}</p>
        <div class="mt-6 space-y-2.5" aria-hidden="true">
            <div class="h-px bg-grid"></div>
            <div class="flex justify-between text-[11px] text-brand-300"><span>{{ __('fields.subtotal') }}</span><span>—</span></div>
            <div class="flex justify-between text-[11px] text-brand-300"><span>{{ __('fields.tax') }}</span><span>—</span></div>
            <div class="h-px bg-grid"></div>
            <div class="flex justify-between text-xs font-bold text-brand-500"><span>{{ __('fields.total') }}</span><span>—</span></div>
        </div>
    </div>
@endif
