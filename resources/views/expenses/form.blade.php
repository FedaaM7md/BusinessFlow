{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($expense = $expense ?? null)
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="label" for="expense_category_id">{{ __('fields.expense_category') }} <span class="text-rose-500">*</span></label>
        <select id="expense_category_id" name="expense_category_id" class="select" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('expense_category_id', $expense?->expense_category_id) === (string) $category->id)>{{ $category->displayName() }}</option>
            @endforeach
        </select>
        @error('expense_category_id')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="supplier_id">{{ __('fields.supplier') }}</label>
        <select id="supplier_id" name="supplier_id" class="select">
            <option value="">{{ __('common.none') }}</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $expense?->supplier_id ?? request('supplier')) === (string) $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-slate-400">{{ __('expenses.supplier_optional') }}</p>
        @error('supplier_id')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="expense_date">{{ __('expenses.expense_date') }} <span class="text-rose-500">*</span></label>
        <x-date-input id="expense_date" name="expense_date" :value="old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        @error('expense_date')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="amount">{{ __('expenses.amount') }} <span class="text-rose-500">*</span></label>
        <input id="amount" name="amount" value="{{ old('amount', $expense ? money_input($expense->amount_minor) : '') }}" class="input" inputmode="decimal" required>
        @error('amount')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="payment_method">{{ __('expenses.method') }} <span class="text-rose-500">*</span></label>
        <select id="payment_method" name="payment_method" class="select" required>
            @foreach(\App\Modules\Expenses\Models\Expense::METHODS as $method)
                <option value="{{ $method }}" @selected(old('payment_method', $expense?->payment_method ?? 'cash') === $method)>{{ __('statuses.'.$method) }}</option>
            @endforeach
        </select>
        @error('payment_method')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="reference">{{ __('fields.reference') }}</label>
        <input id="reference" name="reference" value="{{ old('reference', $expense?->reference) }}" class="input">
        @error('reference')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label class="label" for="description">{{ __('expenses.description') }} <span class="text-rose-500">*</span></label>
        <textarea id="description" name="description" rows="3" class="input" required>{{ old('description', $expense?->description) }}</textarea>
        @error('description')<p class="error-text">{{ $message }}</p>@enderror
    </div>
</div>
