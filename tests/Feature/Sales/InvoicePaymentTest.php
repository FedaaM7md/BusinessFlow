<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Sales;

use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Payment;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    public function test_invoice_totals_tax_and_numbering(): void
    {
        $sales = $this->makeUser('sales');
        $customer = $this->makeCustomer();
        $warehouse = $this->makeWarehouse();

        $payload = [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'discount' => '10.00',
            'lines' => [
                ['description' => 'Line A', 'quantity' => '2', 'unit_price' => '100.00', 'tax_rate' => '14'],
                ['description' => 'Line B', 'quantity' => '1', 'unit_price' => '50.00', 'tax_rate' => '0'],
            ],
        ];

        $this->actingAs($sales)->post('/invoices', $payload)->assertRedirect();
        $invoice = Invoice::query()->latest('id')->firstOrFail();

        $this->assertSame('INV-'.now()->year.'-0001', $invoice->number);
        $this->assertSame('draft', $invoice->status);
        $this->assertSame(25000, $invoice->subtotal_minor);
        $this->assertSame(1000, $invoice->discount_minor);
        $this->assertSame(2688, $invoice->tax_minor);
        $this->assertSame(26688, $invoice->total_minor);
        $this->assertSame(0, $invoice->paidMinor());
        $this->assertSame(26688, $invoice->balanceMinor());

        $this->actingAs($sales)->post('/invoices', $payload)->assertRedirect();
        $this->assertSame('INV-'.now()->year.'-0002', Invoice::query()->latest('id')->firstOrFail()->number);
    }

    public function test_issue_invoice_creates_stock_out_and_cancel_reverses_it(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer();
        $warehouse = $this->makeWarehouse();
        $product = $this->makeProduct();
        app(InventoryService::class)->stockIn($product, $warehouse, 10, $admin, 5000, null, 'opening');

        $this->actingAs($admin)->post('/invoices', [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'lines' => [
                ['product_id' => $product->id, 'description' => 'Tracked sale', 'quantity' => '3', 'unit_price' => '100.00', 'tax_rate' => '14'],
            ],
        ]);
        $invoice = Invoice::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)->post('/invoices/'.$invoice->id.'/issue')->assertRedirect(route('invoices.show', $invoice));
        $this->assertSame('issued', $invoice->fresh()->status);
        $this->assertEquals(7, $product->onHand($warehouse->id));
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'reference_type' => $invoice->getMorphClass(),
            'reference_id' => $invoice->id,
        ]);

        $this->actingAs($admin)->put('/invoices/'.$invoice->id, [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->format('Y-m-d'),
            'lines' => [['description' => 'Changed', 'quantity' => '1', 'unit_price' => '1.00', 'tax_rate' => '0']],
        ])->assertSessionHasErrors('status');

        $this->actingAs($admin)->delete('/invoices/'.$invoice->id)->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);

        $this->actingAs($admin)->post('/invoices/'.$invoice->id.'/cancel', [
            'cancellation_reason' => 'Customer cancelled the demo order.',
        ])->assertRedirect(route('invoices.show', $invoice));

        $this->assertSame('cancelled', $invoice->fresh()->status);
        $this->assertEquals(10, $product->onHand($warehouse->id));
        $this->assertNotNull($invoice->fresh()->cancelled_at);
        $this->assertSame($admin->id, $invoice->fresh()->cancelled_by);
    }

    public function test_invoice_with_payments_cannot_be_cancelled(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer();
        $warehouse = $this->makeWarehouse();
        $invoice = $this->issuedServiceInvoice($admin, $customer, $warehouse, '200.00');

        $this->actingAs($admin)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => '50.00',
            'method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ])->assertRedirect();

        $this->actingAs($admin)->post('/invoices/'.$invoice->id.'/cancel', [
            'cancellation_reason' => 'Attempted cancellation with payment.',
        ])->assertSessionHasErrors('status');

        $this->assertSame('partially_paid', $invoice->fresh()->status);
    }

    public function test_partial_and_full_payments_update_status_and_balance(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer();
        $warehouse = $this->makeWarehouse();
        $invoice = $this->issuedServiceInvoice($admin, $customer, $warehouse, '1000.00');

        $this->actingAs($admin)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => '400.00',
            'method' => 'bank_transfer',
            'payment_date' => now()->format('Y-m-d'),
            'reference' => 'TRX-1',
        ])->assertRedirect();

        $this->assertSame('partially_paid', $invoice->fresh()->status);
        $this->assertSame(40000, $invoice->fresh()->paidMinor());
        $this->assertSame(74000, $invoice->fresh()->balanceMinor());

        $this->actingAs($admin)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => '740.00',
            'method' => 'card',
            'payment_date' => now()->format('Y-m-d'),
        ])->assertRedirect();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame(114000, $invoice->fresh()->paidMinor());
        $this->assertSame(0, $invoice->fresh()->balanceMinor());
        $this->assertSame(2, Payment::query()->where('invoice_id', $invoice->id)->count());
        $this->assertDatabaseHas('payments', ['number' => 'PAY-'.now()->year.'-0001']);
    }

    public function test_overpayment_and_payment_against_draft_are_rejected(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer();
        $warehouse = $this->makeWarehouse();
        $invoice = $this->issuedServiceInvoice($admin, $customer, $warehouse, '100.00');

        $this->actingAs($admin)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => '500.00',
            'method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ])->assertSessionHasErrors('amount');

        $draft = Invoice::create([
            'number' => 'INV-DRAFT-X', 'customer_id' => $customer->id, 'warehouse_id' => $warehouse->id,
            'status' => 'draft', 'issue_date' => now()->format('Y-m-d'), 'total_minor' => 1000,
        ]);

        $this->actingAs($admin)->post('/payments', [
            'invoice_id' => $draft->id,
            'amount' => '1.00',
            'method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
        ])->assertSessionHasErrors('invoice_id');
    }

    public function test_payment_validation(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/payments', [
            'invoice_id' => 99999,
            'amount' => '-5',
            'method' => 'crypto',
            'payment_date' => now()->addDay()->format('Y-m-d'),
        ])->assertSessionHasErrors(['invoice_id', 'amount', 'method', 'payment_date']);
    }

    public function test_printable_invoice_preserves_mixed_arabic_and_english_descriptions(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer(['name' => 'شركة النيل Demo']);
        $warehouse = $this->makeWarehouse();

        $this->actingAs($admin)->post('/invoices', [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->format('Y-m-d'),
            'lines' => [
                ['description' => 'خدمة تركيب — Installation Service', 'quantity' => '1', 'unit_price' => '100.00', 'tax_rate' => '14'],
                ['description' => 'English only line', 'quantity' => '2', 'unit_price' => '50.00', 'tax_rate' => '0'],
            ],
        ]);
        $invoice = Invoice::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)->get('/invoices/'.$invoice->id.'/print')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('خدمة تركيب — Installation Service')
            ->assertSee('English only line')
            ->assertSee('فاتورة');

        $this->actingAs($admin)->post('/locale', ['locale' => 'en']);
        $this->actingAs($admin)->get('/invoices/'.$invoice->id.'/print')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('خدمة تركيب — Installation Service')
            ->assertSee('Invoice');
    }

    private function issuedServiceInvoice($actor, $customer, $warehouse, string $price): Invoice
    {
        $this->actingAs($actor)->post('/invoices', [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'lines' => [
                ['description' => 'Consulting service', 'quantity' => '1', 'unit_price' => $price, 'tax_rate' => '14'],
            ],
        ])->assertRedirect();

        $invoice = Invoice::query()->latest('id')->firstOrFail();
        $this->actingAs($actor)->post('/invoices/'.$invoice->id.'/issue')->assertRedirect(route('invoices.show', $invoice));

        return $invoice->fresh();
    }
}
