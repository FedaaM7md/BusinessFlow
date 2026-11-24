<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Http\Requests\ReportRequest;
use App\Modules\Reports\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $service) {}

    public function index(ReportRequest $request): View
    {
        $this->authorizePermission('reports.view');
        [$from, $to] = $this->range($request);

        $summary = $this->service->summary($from, $to);
        $salesByPeriod = $this->service->salesByPeriod($from, $to);
        $salesByCustomer = $this->service->salesByCustomer($from, $to);
        $salesByProduct = $this->service->salesByProduct($from, $to);
        $aging = $this->service->receivablesAging();
        $expenses = $this->service->expensesByCategory($from, $to);
        $stock = $this->service->stockValuation();
        $stockTotal = $stock->sum('value');

        return view('reports.index', compact(
            'from', 'to', 'summary', 'salesByPeriod', 'salesByCustomer', 'salesByProduct',
            'aging', 'expenses', 'stock', 'stockTotal'
        ));
    }

    public function export(ReportRequest $request, string $type): StreamedResponse|Response
    {
        $this->authorizePermission('reports.view');
        abort_unless(in_array($type, ['sales', 'customers', 'products', 'aging', 'expenses', 'stock'], true), 404);

        [$from, $to] = $this->range($request);
        $csv = $this->service->csvData($type, $from, $to);
        $filename = 'businessflow-'.$type.'-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($csv) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, array_map(fn ($cell) => $this->sanitizeCsvCell($cell), $csv['headers']));
            foreach ($csv['rows'] as $row) {
                fputcsv($output, array_map(fn ($cell) => $this->sanitizeCsvCell($cell), $row));
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Neutralise spreadsheet formula injection. A cell whose first visible
     * character is =, +, -, @, tab or carriage return is prefixed with an
     * apostrophe so Excel/Sheets treat it as text rather than a formula.
     */
    private function sanitizeCsvCell(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $visible = ltrim($value);

        if ($visible !== '' && in_array($visible[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function range(ReportRequest $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->validated('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->validated('to'))->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }
}
