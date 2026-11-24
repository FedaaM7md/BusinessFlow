<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Http\Requests\PaymentRequest;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Payment;
use App\Modules\Sales\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $service) {}

    public function index(Request $request): View
    {
        $this->authorizePermission('payments.view');

        $payments = Payment::query()
            ->with(['customer', 'invoice', 'creator'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->query('q');
                $q->where(fn ($inner) => $inner->where('number', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhereHas('invoice', fn ($i) => $i->where('number', 'like', "%{$term}%"))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")));
            })
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->query('method')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->query('to')))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request): View
    {
        $this->authorizePermission('payments.manage');
        $invoice = $request->filled('invoice') ? Invoice::with('payments')->find($request->query('invoice')) : null;

        $invoices = Invoice::with(['customer', 'payments'])
            ->whereIn('status', ['issued', 'partially_paid'])
            ->orderByDesc('issue_date')
            ->get()
            ->filter(fn ($item) => $item->balanceMinor() > 0);

        return view('payments.create', compact('invoices', 'invoice'));
    }

    public function store(PaymentRequest $request): RedirectResponse
    {
        $this->authorizePermission('payments.manage');
        $payment = $this->service->create($request->validated(), $request->user());

        return redirect()->route('payments.show', $payment)->with('success', __('messages.payment_recorded'));
    }

    public function show(Payment $payment): View
    {
        $this->authorizePermission('payments.view');
        $payment->load(['invoice.customer', 'customer', 'creator']);

        return view('payments.show', compact('payment'));
    }
}
