<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Expenses\Services;

use App\Models\User;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Core\Services\DocumentNumberService;
use App\Modules\Core\Services\Money;
use App\Modules\Expenses\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly DocumentNumberService $numbers,
    ) {}

    public function create(array $data, User $actor): Expense
    {
        return DB::transaction(function () use ($data, $actor) {
            $expense = Expense::create([
                'number' => $this->numbers->next('expense', $data['expense_date']),
                'expense_category_id' => $data['expense_category_id'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'expense_date' => $data['expense_date'],
                'amount_minor' => Money::toMinor($data['amount']),
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'],
                'created_by' => $actor->id,
            ]);

            $this->activity->log('expenses', 'created', 'Expense '.$expense->number.' created', $expense, [
                'amount_minor' => ['from' => null, 'to' => $expense->amount_minor],
            ]);

            return $expense;
        });
    }

    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $expense->update([
                'expense_category_id' => $data['expense_category_id'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'expense_date' => $data['expense_date'],
                'amount_minor' => Money::toMinor($data['amount']),
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'],
            ]);

            $this->activity->log('expenses', 'updated', 'Expense '.$expense->number.' updated', $expense, $this->activity->changedFields($expense));

            return $expense->refresh();
        });
    }

    public function delete(Expense $expense): void
    {
        DB::transaction(function () use ($expense) {
            $number = $expense->number;
            $expense->delete();
            $this->activity->log('expenses', 'deleted', 'Expense '.$number.' deleted');
        });
    }
}
