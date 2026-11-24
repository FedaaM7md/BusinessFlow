<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Expenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expenses\Http\Requests\ExpenseCategoryRequest;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Expenses\Services\ExpenseCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function __construct(private readonly ExpenseCategoryService $service) {}

    public function index(): View
    {
        $this->authorizePermission('expenses.view');
        $categories = ExpenseCategory::withCount('expenses')->orderBy('name')->paginate(20);

        return view('expense-categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorizePermission('expenses.manage');

        return view('expense-categories.create');
    }

    public function store(ExpenseCategoryRequest $request): RedirectResponse
    {
        $this->authorizePermission('expenses.manage');
        $this->service->create($request->validated());

        return redirect()->route('expense-categories.index')->with('success', __('messages.created'));
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        $this->authorizePermission('expenses.manage');

        return view('expense-categories.edit', compact('expenseCategory'));
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->authorizePermission('expenses.manage');
        $this->service->update($expenseCategory, $request->validated());

        return redirect()->route('expense-categories.index')->with('success', __('messages.updated'));
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->authorizePermission('expenses.manage');
        $this->service->delete($expenseCategory);

        return redirect()->route('expense-categories.index')->with('success', __('messages.deleted'));
    }
}
