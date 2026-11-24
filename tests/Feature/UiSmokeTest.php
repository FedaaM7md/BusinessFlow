<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature;

use App\Modules\Catalog\Models\Category;
use App\Modules\CRM\Models\Contact;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Expenses\Services\ExpenseService;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Sales\Services\InvoiceService;
use App\Modules\Sales\Services\PaymentService;
use App\Modules\Sales\Services\QuotationService;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Users\Models\Role;
use Tests\TestCase;

class UiSmokeTest extends TestCase
{
    public function test_all_primary_pages_render_for_an_administrator(): void
    {
        $admin = $this->makeUser('admin');
        $warehouse = $this->makeWarehouse();
        $customer = $this->makeCustomer();
        $supplier = Supplier::create(['code' => 'SUP-SMOKE', 'name' => 'Smoke Supplier', 'is_active' => true]);
        $category = Category::create(['name' => 'Smoke Category']);
        $product = $this->makeProduct(['category_id' => $category->id, 'name' => 'Smoke Product']);
        $contact = Contact::create(['customer_id' => $customer->id, 'name' => 'Smoke Contact']);
        $expenseCategory = ExpenseCategory::create(['name' => 'Smoke Expense', 'is_active' => true]);

        app(InventoryService::class)->stockIn($product, $warehouse, 8, $admin, 1000, null, 'smoke');

        $quotation = app(QuotationService::class)->create([
            'customer_id' => $customer->id,
            'issue_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(5)->format('Y-m-d'),
            'lines' => [['description' => 'Smoke quotation', 'quantity' => '1', 'unit_price' => '50.00', 'tax_rate' => '14']],
        ], $admin);

        $invoice = app(InvoiceService::class)->create([
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [['description' => 'Smoke invoice', 'quantity' => '1', 'unit_price' => '80.00', 'tax_rate' => '14']],
        ], $admin);
        app(InvoiceService::class)->issue($invoice, $admin);

        $payment = app(PaymentService::class)->create([
            'invoice_id' => $invoice->id,
            'amount' => '10.00',
            'method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ], $admin);

        $expense = app(ExpenseService::class)->create([
            'expense_category_id' => $expenseCategory->id,
            'expense_date' => now()->format('Y-m-d'),
            'amount' => '20.00',
            'payment_method' => 'cash',
            'description' => 'Smoke expense',
        ], $admin);

        $salesRole = Role::query()->where('name', 'sales')->firstOrFail();

        $urls = [
            '/dashboard',
            '/customers', '/customers/create', '/customers/'.$customer->id, '/customers/'.$customer->id.'/edit',
            '/customers/'.$customer->id.'/contacts/create', '/customers/'.$customer->id.'/contacts/'.$contact->id.'/edit',
            '/suppliers', '/suppliers/create', '/suppliers/'.$supplier->id, '/suppliers/'.$supplier->id.'/edit',
            '/products', '/products/create', '/products/'.$product->id, '/products/'.$product->id.'/edit',
            '/categories', '/categories/create', '/categories/'.$category->id.'/edit',
            '/warehouses', '/warehouses/create', '/warehouses/'.$warehouse->id, '/warehouses/'.$warehouse->id.'/edit',
            '/inventory', '/inventory/create', '/inventory/low-stock',
            '/quotations', '/quotations/create', '/quotations/'.$quotation->id, '/quotations/'.$quotation->id.'/edit', '/quotations/'.$quotation->id.'/print',
            '/invoices', '/invoices/create', '/invoices/'.$invoice->id, '/invoices/'.$invoice->id.'/print',
            '/payments', '/payments/create', '/payments/'.$payment->id,
            '/expenses', '/expenses/create', '/expenses/'.$expense->id, '/expenses/'.$expense->id.'/edit',
            '/expense-categories', '/expense-categories/create', '/expense-categories/'.$expenseCategory->id.'/edit',
            '/reports',
            '/users', '/users/create', '/users/'.$admin->id.'/edit',
            '/roles', '/roles/'.$salesRole->id.'/edit',
            '/activity',
            '/settings',
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
