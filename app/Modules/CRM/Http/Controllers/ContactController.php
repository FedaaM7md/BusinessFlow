<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Http\Requests\ContactRequest;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $service) {}

    public function create(Customer $customer): View
    {
        $this->authorizePermission('customers.manage');

        return view('contacts.create', compact('customer'));
    }

    public function store(ContactRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorizePermission('customers.manage');
        $this->service->create($customer, $request->validated());

        return redirect()->route('customers.show', $customer)->with('success', __('messages.created'));
    }

    public function edit(Customer $customer, Contact $contact): View
    {
        $this->authorizePermission('customers.manage');
        abort_unless($contact->customer_id === $customer->id, 404);

        return view('contacts.edit', compact('customer', 'contact'));
    }

    public function update(ContactRequest $request, Customer $customer, Contact $contact): RedirectResponse
    {
        $this->authorizePermission('customers.manage');
        abort_unless($contact->customer_id === $customer->id, 404);

        $this->service->update($contact, $request->validated());

        return redirect()->route('customers.show', $customer)->with('success', __('messages.updated'));
    }

    public function destroy(Customer $customer, Contact $contact): RedirectResponse
    {
        $this->authorizePermission('customers.manage');
        abort_unless($contact->customer_id === $customer->id, 404);

        $this->service->delete($contact);

        return redirect()->route('customers.show', $customer)->with('success', __('messages.deleted'));
    }
}
