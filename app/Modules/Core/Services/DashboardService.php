<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Core\Services;

use App\Modules\Catalog\Models\Product;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Sales\Models\Invoice;
use Illuminate\Support\Collection;

class DashboardService
{
    public function data(): array
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $salesThisMonth = (int) Invoice::query()
            ->whereIn('status', ['issued', 'partially_paid', 'paid'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_minor');

        $outstandingInvoices = Invoice::query()
            ->outstanding()
            ->withSum('payments as paid_total', 'amount_minor')
            ->get();

        $outstanding = (int) $outstandingInvoices->sum(fn ($invoice) => max(0, $invoice->total_minor - (int) ($invoice->paid_total ?? 0)));

        $overdue = $outstandingInvoices->filter(fn ($invoice) => $invoice->due_date && $invoice->due_date->isBefore(today())
            && $invoice->total_minor - (int) ($invoice->paid_total ?? 0) > 0);

        $expensesThisMonth = (int) Expense::query()
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount_minor');

        $lowStock = Product::lowStock()
            ->with('category')
            ->orderBy('name')
            ->take(8)
            ->get();

        $lowStockCount = Product::lowStock()->count();

        $recentActivity = ActivityLog::with('user')->latest()->take(8)->get();

        $monthlySales = $this->monthlySales();
        $statusCounts = Invoice::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $topCustomers = Invoice::query()
            ->whereIn('status', ['issued', 'partially_paid', 'paid'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('customer_id, SUM(total_minor) as total')
            ->with('customer:id,name,company_name')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return compact(
            'salesThisMonth',
            'outstanding',
            'overdue',
            'expensesThisMonth',
            'lowStock',
            'lowStockCount',
            'recentActivity',
            'monthlySales',
            'statusCounts',
            'topCustomers',
            'outstandingInvoices',
        );
    }

    /** @return Collection<int, array{label: string, total: int}> */
    private function monthlySales(): Collection
    {
        return collect(range(5, 0))->map(function (int $monthsAgo) {
            $month = now()->subMonthsNoOverflow($monthsAgo);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            return [
                'label' => $start->locale(app()->getLocale())->translatedFormat('M Y'),
                'total' => (int) Invoice::query()
                    ->whereIn('status', ['issued', 'partially_paid', 'paid'])
                    ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
                    ->sum('total_minor'),
            ];
        });
    }
}
