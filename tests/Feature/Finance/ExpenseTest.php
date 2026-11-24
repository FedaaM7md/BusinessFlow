<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Finance;

use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Suppliers\Models\Supplier;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    public function test_expense_categories_crud_and_validation(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/expense-categories', ['name' => 'Rent', 'name_ar' => 'إيجار', 'is_active' => '1'])
            ->assertRedirect('/expense-categories');
        $category = ExpenseCategory::query()->where('name', 'Rent')->firstOrFail();

        $this->actingAs($admin)->post('/expense-categories', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)->put('/expense-categories/'.$category->id, ['name' => 'Office Rent', 'is_active' => '1'])
            ->assertRedirect('/expense-categories');
        $this->assertSame('Office Rent', $category->fresh()->name);

        $expenseCategory = $category;
        Expense::create([
            'number' => 'EXP-LINK', 'expense_category_id' => $expenseCategory->id,
            'expense_date' => now()->format('Y-m-d'), 'amount_minor' => 100,
            'payment_method' => 'cash', 'description' => 'Linked expense',
        ]);
        $this->actingAs($admin)->delete('/expense-categories/'.$category->id)->assertSessionHasErrors('delete');
    }

    public function test_expense_crud_converts_money_and_validates(): void
    {
        $admin = $this->makeUser('admin');
        $category = ExpenseCategory::create(['name' => 'Utilities', 'is_active' => true]);
        $supplier = Supplier::create(['code' => 'SUP-100', 'name' => 'Utility Co', 'is_active' => true]);

        $this->actingAs($admin)->post('/expenses', [
            'expense_category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'expense_date' => now()->format('Y-m-d'),
            'amount' => '1234.56',
            'payment_method' => 'bank_transfer',
            'reference' => 'BILL-1',
            'description' => 'Electricity bill',
        ])->assertRedirect();

        $expense = Expense::query()->where('description', 'Electricity bill')->firstOrFail();
        $this->assertSame(123456, $expense->amount_minor);
        $this->assertSame('EXP-'.now()->year.'-0001', $expense->number);

        $this->actingAs($admin)->put('/expenses/'.$expense->id, [
            'expense_category_id' => $category->id,
            'supplier_id' => null,
            'expense_date' => now()->format('Y-m-d'),
            'amount' => '2000.00',
            'payment_method' => 'cash',
            'description' => 'Updated bill',
        ])->assertRedirect(route('expenses.show', $expense));
        $this->assertSame(200000, $expense->fresh()->amount_minor);

        $this->actingAs($admin)->post('/expenses', [
            'expense_category_id' => 9999,
            'expense_date' => 'not-a-date',
            'amount' => '-4',
            'payment_method' => 'crypto',
            'description' => '',
        ])->assertSessionHasErrors(['expense_category_id', 'expense_date', 'amount', 'payment_method', 'description']);

        $this->actingAs($admin)->delete('/expenses/'.$expense->id)->assertRedirect('/expenses');
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_expense_index_filters_by_category_and_period(): void
    {
        $admin = $this->makeUser('admin');
        $category = ExpenseCategory::create(['name' => 'Marketing', 'is_active' => true]);
        Expense::create([
            'number' => 'EXP-FILTER', 'expense_category_id' => $category->id,
            'expense_date' => '2026-01-15', 'amount_minor' => 50000,
            'payment_method' => 'cash', 'description' => 'Campaign spend',
        ]);

        $this->actingAs($admin)->get('/expenses?category='.$category->id.'&from=2026-01-01&to=2026-01-31')
            ->assertOk()
            ->assertSee('EXP-FILTER')
            ->assertSee('Campaign spend');
    }
}
