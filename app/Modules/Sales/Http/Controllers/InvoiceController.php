<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\CRM\Models\Customer;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Sales\Http\Requests\CancelInvoiceRequest;
use App\Modules\Sales\Http\Requests\InvoiceRequest;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('invoices.view');
        $sort = $this->sortColumn($request->query('sort'), ['number', 'issue_date', 'due_date', 'total_minor', 'status', 'created_at'], 'issue_date');
        $direction = $this->sortDirection($request->query('direction'));

        $invoices = Invoice::query()
            ->with('customer')
            ->withSum('payments as paid_total', 'amount_minor')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->query('q');
                $q->where(fn ($inner) => $inner->where('number', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->query('overdue') === '1', fn ($q) => $q->outstanding()->whereDate('due_date', '<', today()))
            ->when($request->filled('customer'), fn ($q) => $q->where('customer_id', $request->query('customer')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('issue_date', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('issue_date', '<=', $request->query('to')))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'customers', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizePermission('invoices.manage');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();

        return view('invoices.create', compact('customers', 'products', 'warehouses'));
    }

    public function store(InvoiceRequest $request): RedirectResponse
    {
        $this->authorizePermission('invoices.manage');
        $invoice = $this->service->create($request->validated(), $request->user());

        return redirect()->route('invoices.show', $invoice)->with('success', __('messages.created'));
    }

    public function show(Invoice $invoice): View
    {
        $this->authorizePermission('invoices.view');
        $invoice->load(['customer', 'lines.product', 'warehouse', 'creator', 'canceller', 'payments.creator']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $this->authorizePermission('invoices.manage');
        abort_unless($invoice->isDraft(), 403, __('messages.only_draft_editable'));

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $invoice->load('lines');

        return view('invoices.edit', compact('invoice', 'customers', 'products', 'warehouses'));
    }

    public function update(InvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizePermission('invoices.manage');
        $this->service->update($invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)->with('success', __('messages.updated'));
    }

    public function issue(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizePermission('invoices.manage');
        $this->service->issue($invoice, $request->user());

        return redirect()->route('invoices.show', $invoice)->with('success', __('messages.invoice_issued'));
    }

    public function cancel(CancelInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizePermission('invoices.manage');
        $this->service->cancel($invoice, $request->validated('cancellation_reason'), $request->user());

        return redirect()->route('invoices.show', $invoice)->with('success', __('messages.invoice_cancelled'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorizePermission('invoices.manage');
        $this->service->delete($invoice);

        return redirect()->route('invoices.index')->with('success', __('messages.deleted'));
    }

    public function print(Invoice $invoice): View
    {
        $this->authorizePermission('invoices.view');
        $invoice->load(['customer', 'lines.product', 'payments']);

        return view('print.invoice', compact('invoice'));
    }
}
