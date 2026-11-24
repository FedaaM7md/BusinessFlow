<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Reports;

use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Expenses\Services\ExpenseService;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Reports\Services\ReportService;
use App\Modules\Sales\Services\InvoiceService;
use App\Modules\Sales\Services\PaymentService;
use Tests\TestCase;

class ReportsAndDashboardTest extends TestCase
{
    public function test_report_totals_match_live_data(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer();
        $warehouse = $this->makeWarehouse();
        $category = ExpenseCategory::create(['name' => 'Operations', 'is_active' => true]);

        $invoice = app(InvoiceService::class)->create([
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(20)->format('Y-m-d'),
            'lines' => [['description' => 'Monthly service', 'quantity' => '1', 'unit_price' => '1000.00', 'tax_rate' => '14']],
        ], $admin);
        app(InvoiceService::class)->issue($invoice, $admin);
        app(PaymentService::class)->create([
            'invoice_id' => $invoice->id,
            'amount' => '500.00',
            'method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ], $admin);
        app(ExpenseService::class)->create([
            'expense_category_id' => $category->id,
            'expense_date' => now()->format('Y-m-d'),
            'amount' => '300.00',
            'payment_method' => 'cash',
            'description' => 'Operations cost',
        ], $admin);

        $summary = app(ReportService::class)->summary(now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(114000, $summary['sales']);
        $this->assertSame(30000, $summary['expenses']);
        $this->assertSame(84000, $summary['profit']);
        $this->assertSame(64000, $summary['receivables']);

        $this->actingAs($admin)->get('/reports')->assertOk()->assertSee('التقارير', false);
    }

    public function test_receivables_aging_buckets(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer();
        $warehouse = $this->makeWarehouse();

        $invoice = app(InvoiceService::class)->create([
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->subDays(80)->format('Y-m-d'),
            'due_date' => now()->subDays(40)->format('Y-m-d'),
            'lines' => [['description' => 'Overdue service', 'quantity' => '1', 'unit_price' => '200.00', 'tax_rate' => '14']],
        ], $admin);
        app(InvoiceService::class)->issue($invoice, $admin);

        $aging = app(ReportService::class)->receivablesAging();

        $this->assertSame(22800, $aging['31_60']['total']);
        $this->assertSame(1, $aging['31_60']['count']);
        $this->assertSame(0, $aging['current']['total']);
        $this->assertSame(0, $aging['over_90']['total']);
    }

    public function test_stock_valuation_and_csv_export(): void
    {
        $admin = $this->makeUser('admin');
        $warehouse = $this->makeWarehouse();
        $product = $this->makeProduct(['cost_minor' => 6000, 'low_stock_threshold' => 0]);
        app(InventoryService::class)
            ->stockIn($product, $warehouse, 5, $admin, 6000, null, 'opening');

        $valuation = app(ReportService::class)->stockValuation();
        $this->assertSame(30000, $valuation->first()['value']);

        $response = $this->actingAs($admin)->get('/reports/export/stock?from='.now()->startOfMonth()->format('Y-m-d').'&to='.now()->endOfMonth()->format('Y-m-d'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString($product->sku, $content);
        $this->assertStringContainsString($product->name, $content);
    }

    public function test_dashboard_uses_database_kpis_and_low_stock_query(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeCustomer();

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee(__('dashboard.sales_this_month'))
            ->assertSee(__('dashboard.low_stock'));

        $this->makeProduct(['low_stock_threshold' => 3]);
        $this->actingAs($admin)->get('/dashboard')->assertOk();
    }

    public function test_activity_log_page_can_be_filtered(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->get('/customers/create')->assertOk();
        $this->actingAs($admin)->post('/customers', [
            'type' => 'individual', 'name' => 'Audit Customer', 'is_active' => '1',
        ]);
        $this->actingAs($admin)->post('/customers', [
            'type' => 'individual', 'name' => 'Audit Customer 2', 'is_active' => '1',
        ]);

        $this->actingAs($admin)->get('/activity?module=crm')->assertOk()->assertSee('Audit Customer');
        $this->actingAs($admin)->get('/activity?module=crm&from='.now()->format('Y-m-d').'&to='.now()->format('Y-m-d'))
            ->assertOk()
            ->assertSee(__('activity.title'));
    }
}
