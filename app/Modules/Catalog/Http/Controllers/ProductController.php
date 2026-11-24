<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\ProductRequest;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Services\ProductService;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('products.view');
        $sort = $this->sortColumn($request->query('sort'), ['name', 'sku', 'price_minor', 'created_at', 'is_active']);
        $direction = $this->sortDirection($request->query('direction'));

        $products = Product::query()
            ->with('category')
            ->withSum('stockMovements as on_hand_quantity', 'quantity')
            ->search($request->query('q'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->query('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->query('status') === 'active'))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizePermission('products.manage');
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorizePermission('products.manage');
        $product = $this->service->create($request->validated(), $request->user());

        return redirect()->route('products.show', $product)->with('success', __('messages.created'));
    }

    public function show(Product $product): View
    {
        $this->authorizePermission('products.view');
        $product->load('category');
        $movements = $product->stockMovements()->with(['warehouse', 'creator'])->orderByDesc('moved_at')->orderByDesc('id')->paginate(15);
        $warehouses = Warehouse::orderBy('name')->get();
        $warehouseStock = $warehouses->mapWithKeys(fn ($warehouse) => [$warehouse->id => $product->onHand($warehouse->id)]);

        return view('products.show', compact('product', 'movements', 'warehouses', 'warehouseStock'));
    }

    public function edit(Product $product): View
    {
        $this->authorizePermission('products.manage');
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorizePermission('products.manage');
        $this->service->update($product, $request->validated());

        return redirect()->route('products.show', $product)->with('success', __('messages.updated'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizePermission('products.manage');
        $this->service->delete($product);

        return redirect()->route('products.index')->with('success', __('messages.deleted'));
    }
}
