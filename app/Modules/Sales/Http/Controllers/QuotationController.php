<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Product;
use App\Modules\CRM\Models\Customer;
use App\Modules\Sales\Http\Requests\QuotationRequest;
use App\Modules\Sales\Http\Requests\QuotationStatusRequest;
use App\Modules\Sales\Models\Quotation;
use App\Modules\Sales\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function __construct(private readonly QuotationService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('quotations.view');
        $sort = $this->sortColumn($request->query('sort'), ['number', 'issue_date', 'total_minor', 'status', 'created_at'], 'issue_date');
        $direction = $this->sortDirection($request->query('direction'));

        $quotations = Quotation::query()
            ->with('customer')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->query('q');
                $q->where(fn ($inner) => $inner->where('number', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('customer'), fn ($q) => $q->where('customer_id', $request->query('customer')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('issue_date', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('issue_date', '<=', $request->query('to')))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get();

        return view('quotations.index', compact('quotations', 'customers', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorizePermission('quotations.manage');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('quotations.create', compact('customers', 'products'));
    }

    public function store(QuotationRequest $request): RedirectResponse
    {
        $this->authorizePermission('quotations.manage');
        $quotation = $this->service->create($request->validated(), $request->user());

        return redirect()->route('quotations.show', $quotation)->with('success', __('messages.created'));
    }

    public function show(Quotation $quotation): View
    {
        $this->authorizePermission('quotations.view');
        $quotation->load(['customer', 'lines.product', 'creator', 'invoice']);

        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation): View
    {
        $this->authorizePermission('quotations.manage');
        abort_unless($quotation->isDraft(), 403, __('messages.only_draft_editable'));

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $quotation->load('lines');

        return view('quotations.edit', compact('quotation', 'customers', 'products'));
    }

    public function update(QuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->authorizePermission('quotations.manage');
        $this->service->update($quotation, $request->validated());

        return redirect()->route('quotations.show', $quotation)->with('success', __('messages.updated'));
    }

    public function updateStatus(QuotationStatusRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->authorizePermission('quotations.manage');
        $this->service->updateStatus($quotation, $request->validated('status'));

        return redirect()->route('quotations.show', $quotation)->with('success', __('messages.status_updated'));
    }

    public function convert(Quotation $quotation, Request $request): RedirectResponse
    {
        $this->authorizePermission('invoices.manage');
        $invoice = $this->service->convertToInvoice($quotation, $request->user());

        return redirect()->route('invoices.show', $invoice)->with('success', __('messages.quotation_converted'));
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorizePermission('quotations.manage');
        $this->service->delete($quotation);

        return redirect()->route('quotations.index')->with('success', __('messages.deleted'));
    }

    public function print(Quotation $quotation): View
    {
        $this->authorizePermission('quotations.view');
        $quotation->load(['customer', 'lines.product']);

        return view('print.quotation', compact('quotation'));
    }
}
