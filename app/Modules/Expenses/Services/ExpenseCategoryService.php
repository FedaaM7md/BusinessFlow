<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Expenses\Services;

use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Expenses\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseCategoryService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(array $data): ExpenseCategory
    {
        return DB::transaction(function () use ($data) {
            $category = ExpenseCategory::create($data);
            $this->activity->log('expenses', 'category_created', 'Expense category '.$category->name.' created', $category);

            return $category;
        });
    }

    public function update(ExpenseCategory $category, array $data): ExpenseCategory
    {
        return DB::transaction(function () use ($category, $data) {
            $category->fill($data)->save();
            $this->activity->log('expenses', 'category_updated', 'Expense category '.$category->name.' updated', $category, $this->activity->changedFields($category));

            return $category->refresh();
        });
    }

    public function delete(ExpenseCategory $category): void
    {
        DB::transaction(function () use ($category) {
            if ($category->expenses()->exists()) {
                throw ValidationException::withMessages(['delete' => __('messages.category_has_expenses')]);
            }

            $category->delete();
            $this->activity->log('expenses', 'category_deleted', 'Expense category '.$category->name.' deleted', $category);
        });
    }
}
