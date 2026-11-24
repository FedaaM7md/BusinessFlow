<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\CategoryRequest;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    public function index(): View
    {
        $this->authorizePermission('products.view');
        $categories = Category::withCount('products')->orderBy('name')->paginate(20);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorizePermission('products.manage');

        return view('categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->authorizePermission('products.manage');
        $this->service->create($request->validated());

        return redirect()->route('categories.index')->with('success', __('messages.created'));
    }

    public function edit(Category $category): View
    {
        $this->authorizePermission('products.manage');

        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizePermission('products.manage');
        $this->service->update($category, $request->validated());

        return redirect()->route('categories.index')->with('success', __('messages.updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizePermission('products.manage');
        $this->service->delete($category);

        return redirect()->route('categories.index')->with('success', __('messages.deleted'));
    }
}
