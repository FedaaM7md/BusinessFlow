<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Sales;

use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Quotation;
use Tests\TestCase;

class QuotationInvoiceTest extends TestCase
{
    public function test_quotation_totals_tax_discount_and_numbering(): void
    {
        $sales = $this->makeUser('sales');
        $customer = $this->makeCustomer();
        $product = $this->makeProduct(['price_minor' => 10000]);

        $payload = [
            'customer_id' => $customer->id,
            'issue_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(14)->format('Y-m-d'),
            'discount' => '10.00',
            'lines' => [
                ['product_id' => $product->id, 'description' => 'Line A', 'quantity' => '2', 'unit_price' => '100.00', 'tax_rate' => '14'],
                ['product_id' => null, 'description' => 'Line B', 'quantity' => '1', 'unit_price' => '50.00', 'tax_rate' => '0'],
            ],
        ];

        $this->actingAs($sales)->post('/quotations', $payload)->assertRedirect();

        $quotation = Quotation::query()->latest('id')->firstOrFail();
        $this->assertSame('QUO-'.now()->year.'-0001', $quotation->number);
        $this->assertSame(25000, $quotation->subtotal_minor);
        $this->assertSame(1000, $quotation->discount_minor);
        $this->assertSame(2688, $quotation->tax_minor);
        $this->assertSame(26688, $quotation->total_minor);
        $this->assertCount(2, $quotation->lines);
        $this->assertSame(800, $quotation->lines->first()->discount_minor);
        $this->assertSame(2688, $quotation->lines->first()->tax_minor);
    }

    public function test_quotation_validation(): void
    {
        $sales = $this->makeUser('sales');
        $customer = $this->makeCustomer();

        $this->actingAs($sales)->post('/quotations', [
            'customer_id' => $customer->id,
            'issue_date' => now()->format('Y-m-d'),
            'valid_until' => now()->subDay()->format('Y-m-d'),
            'discount' => 'abc',
            'lines' => [
                ['description' => '', 'quantity' => '0', 'unit_price' => 'bad', 'tax_rate' => '120'],
            ],
        ])->assertSessionHasErrors([
            'valid_until', 'discount', 'lines.0.description', 'lines.0.quantity', 'lines.0.unit_price', 'lines.0.tax_rate',
        ]);
    }

    public function test_quotation_status_flow_and_invalid_transition(): void
    {
        $sales = $this->makeUser('sales');
        $customer = $this->makeCustomer();

        $this->actingAs($sales)->post('/quotations', [
            'customer_id' => $customer->id,
            'issue_date' => now()->format('Y-m-d'),
            'lines' => [['description' => 'Service', 'quantity' => '1', 'unit_price' => '100.00', 'tax_rate' => '14']],
        ]);
        $quotation = Quotation::query()->latest('id')->firstOrFail();

        $this->actingAs($sales)->patch('/quotations/'.$quotation->id.'/status', ['status' => 'accepted'])
            ->assertSessionHasErrors('status');
        $this->actingAs($sales)->patch('/quotations/'.$quotation->id.'/status', ['status' => 'sent'])
            ->assertRedirect(route('quotations.show', $quotation));
        $this->assertSame('sent', $quotation->fresh()->status);
        $this->actingAs($sales)->patch('/quotations/'.$quotation->id.'/status', ['status' => 'accepted'])
            ->assertRedirect(route('quotations.show', $quotation));
        $this->assertSame('accepted', $quotation->fresh()->status);
    }

    public function test_accepted_quotation_converts_to_draft_invoice_with_copied_lines(): void
    {
        $sales = $this->makeUser('sales');
        $customer = $this->makeCustomer();
        $this->makeWarehouse();

        $this->actingAs($sales)->post('/quotations', [
            'customer_id' => $customer->id,
            'issue_date' => now()->format('Y-m-d'),
            'lines' => [
                ['description' => 'Item One', 'quantity' => '3', 'unit_price' => '125.00', 'tax_rate' => '14'],
                ['description' => 'Item Two', 'quantity' => '1', 'unit_price' => '300.00', 'tax_rate' => '0'],
            ],
        ]);
        $quotation = Quotation::query()->latest('id')->firstOrFail();
        $this->actingAs($sales)->patch('/quotations/'.$quotation->id.'/status', ['status' => 'sent']);
        $this->actingAs($sales)->patch('/quotations/'.$quotation->id.'/status', ['status' => 'accepted']);

        $this->actingAs($sales)->post('/quotations/'.$quotation->id.'/convert')->assertRedirect();

        $invoice = Invoice::query()->where('quotation_id', $quotation->id)->firstOrFail();
        $this->assertSame('draft', $invoice->status);
        $this->assertSame($quotation->total_minor, $invoice->total_minor);
        $this->assertCount(2, $invoice->lines);
        $this->assertSame('Item One', $invoice->lines->first()->description);

        $this->actingAs($sales)->post('/quotations/'.$quotation->id.'/convert')
            ->assertSessionHasErrors('status');
        $this->assertSame(1, Invoice::query()->where('quotation_id', $quotation->id)->count());
    }

    public function test_only_accepted_quotations_can_be_converted(): void
    {
        $sales = $this->makeUser('sales');
        $customer = $this->makeCustomer();
        $this->makeWarehouse();

        $this->actingAs($sales)->post('/quotations', [
            'customer_id' => $customer->id,
            'issue_date' => now()->format('Y-m-d'),
            'lines' => [['description' => 'Draft service', 'quantity' => '1', 'unit_price' => '100.00', 'tax_rate' => '14']],
        ]);
        $quotation = Quotation::query()->latest('id')->firstOrFail();

        $this->actingAs($sales)->post('/quotations/'.$quotation->id.'/convert')
            ->assertSessionHasErrors('status');
        $this->assertSame(0, Invoice::query()->where('quotation_id', $quotation->id)->count());
    }

    public function test_document_numbers_are_unique_per_type_and_year(): void
    {
        $sales = $this->makeUser('sales');
        $customer = $this->makeCustomer();

        foreach (['First', 'Second', 'Third'] as $description) {
            $this->actingAs($sales)->post('/quotations', [
                'customer_id' => $customer->id,
                'issue_date' => now()->format('Y-m-d'),
                'lines' => [['description' => $description, 'quantity' => '1', 'unit_price' => '10.00', 'tax_rate' => '14']],
            ]);
        }

        $numbers = Quotation::query()->orderBy('id')->pluck('number');
        $this->assertCount(3, $numbers->unique());
        $this->assertSame('QUO-'.now()->year.'-0001', $numbers[0]);
        $this->assertSame('QUO-'.now()->year.'-0002', $numbers[1]);
        $this->assertSame('QUO-'.now()->year.'-0003', $numbers[2]);
    }
}
