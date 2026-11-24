<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\CRM;

use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Suppliers\Models\Supplier;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    public function test_supplier_crud_and_validation(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/suppliers', [
            'name' => 'Acme Supplies',
            'contact_name' => 'Sara',
            'email' => 'sara@example.test',
            'phone' => '123',
            'is_active' => '1',
        ])->assertRedirect();

        $supplier = Supplier::query()->where('name', 'Acme Supplies')->firstOrFail();
        $this->assertStringStartsWith('SUP-', $supplier->code);

        $this->actingAs($admin)->put('/suppliers/'.$supplier->id, [
            'name' => 'Acme Supplies Updated',
            'email' => 'bad-email',
            'is_active' => '1',
        ])->assertSessionHasErrors('email');

        $this->actingAs($admin)->put('/suppliers/'.$supplier->id, [
            'name' => 'Acme Supplies Updated',
            'email' => 'ok@example.test',
            'is_active' => '0',
        ])->assertRedirect(route('suppliers.show', $supplier));
        $this->assertSame('Acme Supplies Updated', $supplier->fresh()->name);
        $this->assertFalse($supplier->fresh()->is_active);

        $this->actingAs($admin)->delete('/suppliers/'.$supplier->id)->assertRedirect('/suppliers');
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_supplier_with_expenses_cannot_be_deleted(): void
    {
        $admin = $this->makeUser('admin');
        $supplier = Supplier::create(['code' => 'SUP-X', 'name' => 'Linked Supplier', 'is_active' => true]);
        $category = ExpenseCategory::create(['name' => 'Rent', 'is_active' => true]);
        Expense::create([
            'number' => 'EXP-X', 'expense_category_id' => $category->id, 'supplier_id' => $supplier->id,
            'expense_date' => now()->format('Y-m-d'), 'amount_minor' => 1000,
            'payment_method' => 'cash', 'description' => 'Test',
        ]);

        $this->actingAs($admin)->delete('/suppliers/'.$supplier->id)->assertSessionHasErrors('delete');
        $this->assertNotSoftDeleted('suppliers', ['id' => $supplier->id]);
    }
}
