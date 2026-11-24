<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Http\Requests\CustomerRequest;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('customers.view');

        $sort = $this->sortColumn($request->query('sort'), ['name', 'code', 'email', 'created_at', 'is_active']);
        $direction = $this->sortDirection($request->query('direction'));

        $customers = Customer::query()
            ->withCount(['invoices', 'contacts'])
            ->search($request->query('q'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->query('status') === 'active'))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizePermission('customers.manage');

        return view('customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $this->authorizePermission('customers.manage');

        $customer = $this->service->create($request->validated(), $request->user());

        return redirect()->route('customers.show', $customer)->with('success', __('messages.created'));
    }

    public function show(Customer $customer): View
    {
        $this->authorizePermission('customers.view');

        $customer->load(['contacts' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('name')]);
        $invoices = $customer->invoices()->with('payments')->orderByDesc('issue_date')->orderByDesc('id')->get();
        $payments = $customer->payments()->with('invoice')->orderByDesc('payment_date')->orderByDesc('id')->get();

        $statement = $invoices->map(fn ($invoice) => [
            'date' => $invoice->issue_date,
            'type' => 'invoice',
            'number' => $invoice->number,
            'debit' => in_array($invoice->status, ['draft', 'cancelled'], true) ? 0 : $invoice->total_minor,
            'credit' => 0,
            'link' => route('invoices.show', $invoice),
        ])->concat($payments->map(fn ($payment) => [
            'date' => $payment->payment_date,
            'type' => 'payment',
            'number' => $payment->number,
            'debit' => 0,
            'credit' => $payment->amount_minor,
            'link' => route('payments.show', $payment),
        ]))->sortBy(fn ($row) => $row['date']->format('Y-m-d').'-'.$row['number'])->values();

        $totals = [
            'invoiced' => $customer->totalInvoicedMinor(),
            'paid' => $customer->totalPaidMinor(),
            'balance' => $customer->balanceMinor(),
        ];

        return view('customers.show', compact('customer', 'invoices', 'payments', 'statement', 'totals'));
    }

    public function edit(Customer $customer): View
    {
        $this->authorizePermission('customers.manage');

        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorizePermission('customers.manage');

        $this->service->update($customer, $request->validated());

        return redirect()->route('customers.show', $customer)->with('success', __('messages.updated'));
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorizePermission('customers.manage');

        $this->service->delete($customer);

        return redirect()->route('customers.index')->with('success', __('messages.deleted'));
    }
}
