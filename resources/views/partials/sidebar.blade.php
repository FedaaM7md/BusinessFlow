{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($user = auth()->user())
<aside
    id="app-sidebar"
    class="app-sidebar fixed inset-y-0 start-0 z-50 flex w-72 max-w-[85vw] flex-col border-e border-grid bg-white text-brand-800 shadow-sm"
    :class="open ? 'is-open' : ''"
    x-effect="open ? scheduleDrawerFocus() : null"
    :role="open ? 'dialog' : null"
    :aria-modal="open ? 'true' : 'false'"
    aria-label="{{ __('nav.main_navigation') }}"
>
    <div class="flex h-16 items-center justify-between border-b border-grid px-4">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3" @click="hide">
            <x-logo class="h-9 w-9 shrink-0" />
            <div class="min-w-0">
                <p class="truncate text-sm font-bold leading-tight text-brand-900">{{ __('common.app_name') }}</p>
                <p class="truncate text-[11px] text-brand-400">{{ __('common.app_descriptor') }}</p>
            </div>
        </a>
        <button type="button" class="rounded-md p-2 text-brand-500 hover:bg-brand-50 lg:hidden" @click="hide" aria-label="{{ __('common.close') }}">
            <x-icon name="close" class="h-5 w-5" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 pb-4" aria-label="{{ __('nav.primary') }}" @click="hide">
        @can('dashboard.view')
            <div class="pt-4">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard">{{ __('nav.dashboard') }}</x-nav-link>
            </div>
        @endcan

        @canany(['customers.view', 'suppliers.view'])
            <p class="nav-group-label">{{ __('nav.crm') }}</p>
            @can('customers.view')
                <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*', 'contacts.*')" icon="customers">{{ __('nav.customers') }}</x-nav-link>
            @endcan
            @can('suppliers.view')
                <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')" icon="suppliers">{{ __('nav.suppliers') }}</x-nav-link>
            @endcan
        @endcanany

        @canany(['quotations.view', 'invoices.view', 'payments.view'])
            <p class="nav-group-label">{{ __('nav.sales') }}</p>
            @can('quotations.view')
                <x-nav-link :href="route('quotations.index')" :active="request()->routeIs('quotations.*')" icon="quotations">{{ __('nav.quotations') }}</x-nav-link>
            @endcan
            @can('invoices.view')
                <x-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" icon="invoices">{{ __('nav.invoices') }}</x-nav-link>
            @endcan
            @can('payments.view')
                <x-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')" icon="payments">{{ __('nav.payments') }}</x-nav-link>
            @endcan
        @endcanany

        @canany(['expenses.view'])
            <p class="nav-group-label">{{ __('nav.finance') }}</p>
            @can('expenses.view')
                <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')" icon="expenses">{{ __('nav.expenses') }}</x-nav-link>
                <x-nav-link :href="route('expense-categories.index')" :active="request()->routeIs('expense-categories.*')" icon="categories">{{ __('nav.expense_categories') }}</x-nav-link>
            @endcan
        @endcanany

        @canany(['products.view', 'inventory.view'])
            <p class="nav-group-label">{{ __('nav.operations') }}</p>
            @can('products.view')
                <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="products">{{ __('nav.products') }}</x-nav-link>
                <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" icon="categories">{{ __('nav.categories') }}</x-nav-link>
            @endcan
            @can('inventory.view')
                <x-nav-link :href="route('warehouses.index')" :active="request()->routeIs('warehouses.*')" icon="inventory">{{ __('nav.warehouses') }}</x-nav-link>
                <x-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.index', 'inventory.create')" icon="movements">{{ __('nav.stock_movements') }}</x-nav-link>
                <x-nav-link :href="route('inventory.low-stock')" :active="request()->routeIs('inventory.low-stock')" icon="alert">{{ __('nav.low_stock') }}</x-nav-link>
            @endcan
        @endcanany

        @can('reports.view')
            <p class="nav-group-label">{{ __('nav.insights') }}</p>
            <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="reports">{{ __('nav.reports') }}</x-nav-link>
        @endcan

        @canany(['users.view', 'roles.manage', 'activity.view', 'settings.manage'])
            <p class="nav-group-label">{{ __('nav.administration') }}</p>
            @can('users.view')
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon="users">{{ __('nav.users') }}</x-nav-link>
            @endcan
            @can('roles.manage')
                <x-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" icon="roles">{{ __('nav.roles') }}</x-nav-link>
            @endcan
            @can('activity.view')
                <x-nav-link :href="route('activity.index')" :active="request()->routeIs('activity.*')" icon="activity">{{ __('nav.activity') }}</x-nav-link>
            @endcan
            @can('settings.manage')
                <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')" icon="settings">{{ __('nav.settings') }}</x-nav-link>
            @endcan
        @endcanany
    </nav>

    @if(demo_enabled())
        <div class="border-t border-grid p-4">
            <div class="rounded-md border border-accent-200 bg-accent-50 p-3">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-accent-500"></span>
                    <p class="text-xs font-semibold text-accent-700">{{ __('common.demo_build') }}</p>
                </div>
                <p class="mt-1 text-[11px] leading-5 text-accent-600">{{ __('common.demo_reset_note') }}</p>
            </div>
        </div>
    @endif
</aside>
