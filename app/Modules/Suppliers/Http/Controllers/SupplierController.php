<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Suppliers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Suppliers\Http\Requests\SupplierRequest;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Suppliers\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('suppliers.view');

        $sort = $this->sortColumn($request->query('sort'), ['name', 'code', 'email', 'created_at', 'is_active']);
        $direction = $this->sortDirection($request->query('direction'));

        $suppliers = Supplier::query()
            ->withCount('expenses')
            ->search($request->query('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->query('status') === 'active'))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizePermission('suppliers.manage');

        return view('suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $this->authorizePermission('suppliers.manage');
        $supplier = $this->service->create($request->validated(), $request->user());

        return redirect()->route('suppliers.show', $supplier)->with('success', __('messages.created'));
    }

    public function show(Supplier $supplier): View
    {
        $this->authorizePermission('suppliers.view');
        $expenses = $supplier->expenses()->with('category')->orderByDesc('expense_date')->paginate(10);
        $expenseTotal = (int) $supplier->expenses()->sum('amount_minor');

        return view('suppliers.show', compact('supplier', 'expenses', 'expenseTotal'));
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorizePermission('suppliers.manage');

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorizePermission('suppliers.manage');
        $this->service->update($supplier, $request->validated());

        return redirect()->route('suppliers.show', $supplier)->with('success', __('messages.updated'));
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorizePermission('suppliers.manage');
        $this->service->delete($supplier);

        return redirect()->route('suppliers.index')->with('success', __('messages.deleted'));
    }
}
