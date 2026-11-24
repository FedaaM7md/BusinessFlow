<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Reports\Services;

use App\Modules\Catalog\Models\Product;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\InvoiceLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    private const SALES_STATUSES = ['issued', 'partially_paid', 'paid'];

    public function salesByPeriod(Carbon $from, Carbon $to): Collection
    {
        return Invoice::query()
            ->whereIn('status', self::SALES_STATUSES)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('issue_date')
            ->get()
            ->groupBy(fn ($invoice) => $invoice->issue_date->format('Y-m'))
            ->map(function ($invoices, $period) {
                return [
                    'period' => $period,
                    'invoices' => $invoices->count(),
                    'subtotal' => (int) $invoices->sum('subtotal_minor'),
                    'tax' => (int) $invoices->sum('tax_minor'),
                    'total' => (int) $invoices->sum('total_minor'),
                ];
            })->values();
    }

    public function salesByCustomer(Carbon $from, Carbon $to): Collection
    {
        return Invoice::query()
            ->selectRaw('customer_id, COUNT(*) as invoices, SUM(subtotal_minor) as subtotal, SUM(tax_minor) as tax, SUM(total_minor) as total')
            ->with('customer:id,name,company_name')
            ->whereIn('status', self::SALES_STATUSES)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->get();
    }

    public function salesByProduct(Carbon $from, Carbon $to): Collection
    {
        return InvoiceLine::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_lines.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'invoice_lines.product_id')
            ->whereIn('invoices.status', self::SALES_STATUSES)
            ->whereBetween('invoices.issue_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('COALESCE(products.name, invoice_lines.description) as name, SUM(invoice_lines.quantity) as quantity, SUM(invoice_lines.subtotal_minor) as subtotal, SUM(invoice_lines.tax_minor) as tax, SUM(invoice_lines.total_minor) as total')
            ->groupBy('invoice_lines.product_id', 'invoice_lines.description', 'products.name')
            ->orderByDesc('total')
            ->get();
    }

    public function receivablesAging(): array
    {
        $buckets = [
            'current' => ['label' => '0-30', 'total' => 0, 'count' => 0, 'invoices' => []],
            '31_60' => ['label' => '31-60', 'total' => 0, 'count' => 0, 'invoices' => []],
            '61_90' => ['label' => '61-90', 'total' => 0, 'count' => 0, 'invoices' => []],
            'over_90' => ['label' => '90+', 'total' => 0, 'count' => 0, 'invoices' => []],
        ];

        $invoices = Invoice::query()
            ->outstanding()
            ->with(['customer:id,name,company_name'])
            ->withSum('payments as paid_total', 'amount_minor')
            ->orderBy('due_date')
            ->get();

        foreach ($invoices as $invoice) {
            $balance = $invoice->total_minor - (int) ($invoice->paid_total ?? 0);
            if ($balance <= 0) {
                continue;
            }

            $days = $invoice->due_date ? max(0, $invoice->due_date->diffInDays(today(), false)) : 0;
            $key = match (true) {
                $days <= 30 => 'current',
                $days <= 60 => '31_60',
                $days <= 90 => '61_90',
                default => 'over_90',
            };

            $buckets[$key]['total'] += $balance;
            $buckets[$key]['count']++;
            $buckets[$key]['invoices'][] = [
                'invoice' => $invoice,
                'balance' => $balance,
                'days' => $days,
            ];
        }

        return $buckets;
    }

    public function expensesByCategory(Carbon $from, Carbon $to): Collection
    {
        return Expense::query()
            ->selectRaw('expense_category_id, COUNT(*) as count, SUM(amount_minor) as total')
            ->with('category:id,name,name_ar')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->get();
    }

    public function stockValuation(): Collection
    {
        return Product::query()
            ->where('is_tracked', true)
            ->with('category')
            ->withSum('stockMovements as on_hand_quantity', 'quantity')
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) {
                $quantity = (float) ($product->on_hand_quantity ?? 0);
                $value = (int) round($quantity * $product->cost_minor);

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_cost' => $product->cost_minor,
                    'value' => $value,
                ];
            });
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $sales = (int) Invoice::query()
            ->whereIn('status', self::SALES_STATUSES)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_minor');

        $expenses = (int) Expense::query()
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount_minor');

        $receivableInvoices = Invoice::query()
            ->outstanding()
            ->withSum('payments as paid_total', 'amount_minor')
            ->get();
        $receivables = (int) $receivableInvoices->sum(fn ($invoice) => max(0, $invoice->total_minor - (int) ($invoice->paid_total ?? 0)));

        return [
            'sales' => $sales,
            'expenses' => $expenses,
            'profit' => $sales - $expenses,
            'receivables' => $receivables,
        ];
    }

    public function stockValueTotal(): int
    {
        return (int) $this->stockValuation()->sum('value');
    }

    /** @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>} */
    public function csvData(string $type, Carbon $from, Carbon $to): array
    {
        return match ($type) {
            'sales' => [
                'headers' => [__('reports.period'), __('reports.invoices'), __('fields.subtotal'), __('fields.tax'), __('fields.total')],
                'rows' => $this->salesByPeriod($from, $to)->map(fn ($row) => [
                    $row['period'], $row['invoices'], MoneyFormatter::decimal($row['subtotal']), MoneyFormatter::decimal($row['tax']), MoneyFormatter::decimal($row['total']),
                ])->all(),
            ],
            'customers' => [
                'headers' => [__('fields.customer'), __('reports.invoices'), __('fields.subtotal'), __('fields.tax'), __('fields.total')],
                'rows' => $this->salesByCustomer($from, $to)->map(fn ($row) => [
                    $row->customer?->name ?? '-', $row->invoices, MoneyFormatter::decimal($row->subtotal), MoneyFormatter::decimal($row->tax), MoneyFormatter::decimal($row->total),
                ])->all(),
            ],
            'products' => [
                'headers' => [__('fields.product'), __('fields.quantity'), __('fields.subtotal'), __('fields.tax'), __('fields.total')],
                'rows' => $this->salesByProduct($from, $to)->map(fn ($row) => [
                    $row->name, (float) $row->quantity, MoneyFormatter::decimal($row->subtotal), MoneyFormatter::decimal($row->tax), MoneyFormatter::decimal($row->total),
                ])->all(),
            ],
            'aging' => [
                'headers' => [__('reports.bucket'), __('fields.invoice'), __('fields.customer'), __('fields.due_date'), __('fields.balance'), __('reports.days_overdue')],
                'rows' => collect($this->receivablesAging())->flatMap(fn ($bucket) => collect($bucket['invoices'])->map(fn ($entry) => [
                    $bucket['label'], $entry['invoice']->number, $entry['invoice']->customer?->name ?? '-',
                    $entry['invoice']->due_date?->format('Y-m-d') ?? '-', MoneyFormatter::decimal($entry['balance']), $entry['days'],
                ]))->values()->all(),
            ],
            'expenses' => [
                'headers' => [__('fields.category'), __('reports.count'), __('fields.amount')],
                'rows' => $this->expensesByCategory($from, $to)->map(fn ($row) => [
                    $row->category?->displayName() ?? '-', $row->count, MoneyFormatter::decimal($row->total),
                ])->all(),
            ],
            'stock' => [
                'headers' => [__('fields.product'), __('fields.sku'), __('fields.quantity'), __('fields.cost'), __('reports.value')],
                'rows' => $this->stockValuation()->map(fn ($row) => [
                    $row['product']->displayName(), $row['product']->sku, (float) $row['quantity'],
                    MoneyFormatter::decimal($row['unit_cost']), MoneyFormatter::decimal($row['value']),
                ])->all(),
            ],
            default => ['headers' => [], 'rows' => []],
        };
    }
}

final class MoneyFormatter
{
    public static function decimal(int $minor): string
    {
        $abs = abs($minor);
        $prefix = $minor < 0 ? '-' : '';

        return $prefix.intdiv($abs, 100).'.'.str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);
    }
}
