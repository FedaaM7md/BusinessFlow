<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\Core\Services\Money;
use App\Modules\Inventory\Http\Requests\StockMovementRequest;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function __construct(private readonly InventoryService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('inventory.view');

        $movements = StockMovement::query()
            ->with(['product', 'warehouse', 'creator'])
            ->when($request->filled('product'), fn ($q) => $q->where('product_id', $request->query('product')))
            ->when($request->filled('warehouse'), fn ($q) => $q->where('warehouse_id', $request->query('warehouse')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('moved_at', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('moved_at', '<=', $request->query('to')))
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $products = Product::where('is_tracked', true)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('inventory.index', compact('movements', 'products', 'warehouses'));
    }

    public function create(): View
    {
        $this->authorizePermission('inventory.manage');
        $products = Product::where('is_tracked', true)->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.create', compact('products', 'warehouses'));
    }

    public function store(StockMovementRequest $request): RedirectResponse
    {
        $this->authorizePermission('inventory.manage');

        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $actor = $request->user();
        $date = $data['moved_at'];

        match ($data['type']) {
            'in' => $this->service->stockIn(
                $product,
                $warehouse,
                $data['quantity'],
                $actor,
                isset($data['unit_cost']) ? Money::toMinor($data['unit_cost']) : $product->cost_minor,
                $date,
                $data['note'] ?? null,
            ),
            'out' => $this->service->stockOut($product, $warehouse, $data['quantity'], $actor, $date, $data['note'] ?? null),
            'adjustment' => $this->service->adjust($product, $warehouse, $data['quantity'], $actor, $data['note'] ?? null, $date),
            'transfer' => $this->service->transfer(
                $product,
                $warehouse,
                Warehouse::findOrFail($data['target_warehouse_id']),
                $data['quantity'],
                $actor,
                $data['note'] ?? null,
                $date,
            ),
        };

        return redirect()->route('inventory.index')->with('success', __('messages.stock_recorded'));
    }

    public function lowStock(): View
    {
        $this->authorizePermission('inventory.view');
        $products = Product::lowStock()
            ->with('category')
            ->orderBy('name')
            ->paginate(20);

        return view('inventory.low-stock', compact('products'));
    }
}
