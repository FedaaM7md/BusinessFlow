<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Category;
use App\Modules\Core\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $category = Category::create($data);
            $this->activity->log('catalog', 'created', 'Category '.$category->name.' created', $category);

            return $category;
        });
    }

    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $category->fill($data)->save();
            $this->activity->log('catalog', 'updated', 'Category '.$category->name.' updated', $category, $this->activity->changedFields($category));

            return $category->refresh();
        });
    }

    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            if ($category->products()->exists()) {
                throw ValidationException::withMessages(['delete' => __('messages.category_has_products')]);
            }

            $category->delete();
            $this->activity->log('catalog', 'deleted', 'Category '.$category->name.' deleted', $category);
        });
    }
}
