<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Expenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expenses\Http\Requests\ExpenseRequest;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Expenses\Services\ExpenseService;
use App\Modules\Suppliers\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('expenses.view');
        $sort = $this->sortColumn($request->query('sort'), ['number', 'expense_date', 'amount_minor', 'created_at'], 'expense_date');
        $direction = $this->sortDirection($request->query('direction'));

        $expenses = Expense::query()
            ->with(['category', 'supplier'])
            ->search($request->query('q'))
            ->when($request->filled('category'), fn ($q) => $q->where('expense_category_id', $request->query('category')))
            ->when($request->filled('supplier'), fn ($q) => $q->where('supplier_id', $request->query('supplier')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->query('to')))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategory::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'categories', 'suppliers', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizePermission('expenses.manage');
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('expenses.create', compact('categories', 'suppliers'));
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $this->authorizePermission('expenses.manage');
        $expense = $this->service->create($request->validated(), $request->user());

        return redirect()->route('expenses.show', $expense)->with('success', __('messages.created'));
    }

    public function show(Expense $expense): View
    {
        $this->authorizePermission('expenses.view');
        $expense->load(['category', 'supplier', 'creator']);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        $this->authorizePermission('expenses.manage');
        $categories = ExpenseCategory::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories', 'suppliers'));
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorizePermission('expenses.manage');
        $this->service->update($expense, $request->validated());

        return redirect()->route('expenses.show', $expense)->with('success', __('messages.updated'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorizePermission('expenses.manage');
        $this->service->delete($expense);

        return redirect()->route('expenses.index')->with('success', __('messages.deleted'));
    }
}
