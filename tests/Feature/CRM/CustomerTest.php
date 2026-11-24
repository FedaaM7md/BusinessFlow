<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\CRM;

use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Customer;
use App\Modules\Sales\Models\Invoice;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    public function test_customer_crud_and_generated_code(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/customers', [
            'type' => 'company',
            'name' => 'Nile Logistics',
            'company_name' => 'Nile Logistics LLC',
            'email' => 'nile@example.test',
            'phone' => '+20 100 111 2222',
            'address' => '1 Demo Street',
            'city' => 'Cairo',
            'country' => 'Egypt',
            'tax_number' => 'TAX-123',
            'is_active' => '1',
        ])->assertRedirect();

        $customer = Customer::query()->where('name', 'Nile Logistics')->firstOrFail();
        $this->assertSame('CUS-00001', $customer->code);
        $this->assertTrue($customer->is_active);

        $this->actingAs($admin)->put('/customers/'.$customer->id, [
            'type' => 'company',
            'name' => 'Nile Logistics Updated',
            'email' => 'new@example.test',
            'is_active' => '1',
        ])->assertRedirect(route('customers.show', $customer));

        $this->assertSame('Nile Logistics Updated', $customer->fresh()->name);
        $this->assertDatabaseHas('activity_logs', ['module' => 'crm', 'action' => 'updated']);

        $this->actingAs($admin)->get('/customers/'.$customer->id)->assertOk()->assertSee('Nile Logistics Updated');
        $this->actingAs($admin)->delete('/customers/'.$customer->id)->assertRedirect('/customers');
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_customer_validation_rejects_bad_data(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/customers', [
            'type' => 'invalid',
            'name' => '',
            'email' => 'not-an-email',
        ])->assertSessionHasErrors(['type', 'name', 'email']);
    }

    public function test_customer_search_filters_and_sort_are_applied(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeCustomer(['name' => 'Alpha Customer']);
        $this->makeCustomer(['name' => 'Beta Customer', 'type' => 'individual', 'is_active' => false]);

        $this->actingAs($admin)->get('/customers?q=Alpha&type=company&status=active&sort=name&direction=asc')
            ->assertOk()
            ->assertSee('Alpha Customer')
            ->assertDontSee('Beta Customer');
    }

    public function test_customer_with_documents_cannot_be_deleted(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer();
        Invoice::create([
            'number' => 'INV-TEST-001', 'customer_id' => $customer->id, 'status' => 'draft',
            'issue_date' => now()->format('Y-m-d'), 'total_minor' => 100,
        ]);

        $this->actingAs($admin)->delete('/customers/'.$customer->id)
            ->assertSessionHasErrors('delete');

        $this->assertNotSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_contacts_can_be_created_updated_and_deleted(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeCustomer();

        $this->actingAs($admin)->post('/customers/'.$customer->id.'/contacts', [
            'name' => 'Primary Person',
            'position' => 'Buyer',
            'email' => 'buyer@example.test',
            'phone' => '12345',
            'is_primary' => '1',
        ])->assertRedirect(route('customers.show', $customer));

        $contact = Contact::query()->where('customer_id', $customer->id)->firstOrFail();
        $this->assertTrue($contact->is_primary);

        $this->actingAs($admin)->put('/customers/'.$customer->id.'/contacts/'.$contact->id, [
            'name' => 'Updated Person',
            'email' => 'updated@example.test',
            'is_primary' => '1',
        ])->assertRedirect(route('customers.show', $customer));
        $this->assertSame('Updated Person', $contact->fresh()->name);

        $this->actingAs($admin)->delete('/customers/'.$customer->id.'/contacts/'.$contact->id)
            ->assertRedirect(route('customers.show', $customer));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_contact_from_another_customer_is_not_accessible(): void
    {
        $admin = $this->makeUser('admin');
        $customerA = $this->makeCustomer();
        $customerB = $this->makeCustomer();
        $contact = Contact::create(['customer_id' => $customerB->id, 'name' => 'Other Person']);

        $this->actingAs($admin)->get('/customers/'.$customerA->id.'/contacts/'.$contact->id.'/edit')
            ->assertNotFound();
    }
}
