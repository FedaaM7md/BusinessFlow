<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\Inventory\Http\Requests\WarehouseRequest;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(private readonly WarehouseService $service) {}

    public function index(): View
    {
        $this->authorizePermission('inventory.view');
        $warehouses = Warehouse::withCount('movements')->orderByDesc('is_default')->orderBy('name')->paginate(20);

        return view('warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        $this->authorizePermission('inventory.manage');

        return view('warehouses.create');
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        $this->authorizePermission('inventory.manage');
        $this->service->create($request->validated());

        return redirect()->route('warehouses.index')->with('success', __('messages.created'));
    }

    public function show(Warehouse $warehouse): View
    {
        $this->authorizePermission('inventory.view');
        $products = Product::query()
            ->where('is_tracked', true)
            ->with('category')
            ->withSum(['stockMovements as warehouse_quantity' => fn ($q) => $q->where('warehouse_id', $warehouse->id)], 'quantity')
            ->orderBy('name')
            ->paginate(20);

        return view('warehouses.show', compact('warehouse', 'products'));
    }

    public function edit(Warehouse $warehouse): View
    {
        $this->authorizePermission('inventory.manage');

        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorizePermission('inventory.manage');
        $this->service->update($warehouse, $request->validated());

        return redirect()->route('warehouses.index')->with('success', __('messages.updated'));
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorizePermission('inventory.manage');
        $this->service->delete($warehouse);

        return redirect()->route('warehouses.index')->with('success', __('messages.deleted'));
    }
}
