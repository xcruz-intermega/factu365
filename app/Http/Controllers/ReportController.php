<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    // ─── Sales Reports ───

    public function salesByClient(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $data = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->select(
                'client_id',
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(tax_base) as total_base'),
                DB::raw('SUM(total_vat) as total_vat'),
                DB::raw('SUM(total_irpf) as total_irpf'),
                DB::raw('SUM(total) as total_amount'),
            )
            ->groupBy('client_id')
            ->with('client:id,legal_name,trade_name,nif')
            ->orderByDesc('total_amount')
            ->get();

        $totals = [
            'invoice_count' => $data->sum('invoice_count'),
            'total_base' => round($data->sum('total_base'), 2),
            'total_vat' => round($data->sum('total_vat'), 2),
            'total_irpf' => round($data->sum('total_irpf'), 2),
            'total_amount' => round($data->sum('total_amount'), 2),
        ];

        return Inertia::render('Reports/Sales/ByClient', [
            'data' => $data,
            'totals' => $totals,
            'filters' => [
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ],
        ]);
    }

    public function salesByProduct(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $data = DocumentLine::whereHas('document', function ($q) use ($dateFrom, $dateTo) {
            $q->issued()
              ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
              ->where('status', '!=', Document::STATUS_DRAFT)
              ->whereBetween('issue_date', [$dateFrom, $dateTo]);
        })
            ->select(
                'product_id',
                'concept',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_subtotal) as total_subtotal'),
                DB::raw('SUM(vat_amount) as total_vat'),
                DB::raw('SUM(line_total) as total_amount'),
            )
            ->groupBy('product_id', 'concept')
            ->with('product:id,name,reference')
            ->orderByDesc('total_amount')
            ->get();

        $totals = [
            'total_subtotal' => round($data->sum('total_subtotal'), 2),
            'total_vat' => round($data->sum('total_vat'), 2),
            'total_amount' => round($data->sum('total_amount'), 2),
        ];

        return Inertia::render('Reports/Sales/ByProduct', [
            'data' => $data,
            'totals' => $totals,
            'filters' => [
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ],
        ]);
    }

    public function salesByPeriod(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $groupBy = $request->input('group_by', 'month'); // month or quarter

        $query = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo]);

        if ($groupBy === 'quarter') {
            $data = $query->select(
                DB::raw('YEAR(issue_date) as year'),
                DB::raw('QUARTER(issue_date) as quarter'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(tax_base) as total_base'),
                DB::raw('SUM(total_vat) as total_vat'),
                DB::raw('SUM(total_irpf) as total_irpf'),
                DB::raw('SUM(total) as total_amount'),
            )
                ->groupBy('year', 'quarter')
                ->orderBy('year')
                ->orderBy('quarter')
                ->get()
                ->map(fn ($row) => [
                    ...$row->toArray(),
                    'label' => $row->year . '-T' . $row->quarter,
                ]);
        } else {
            $data = $query->select(
                DB::raw('YEAR(issue_date) as year'),
                DB::raw('MONTH(issue_date) as month'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(tax_base) as total_base'),
                DB::raw('SUM(total_vat) as total_vat'),
                DB::raw('SUM(total_irpf) as total_irpf'),
                DB::raw('SUM(total) as total_amount'),
            )
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(fn ($row) => [
                    ...$row->toArray(),
                    'label' => Carbon::createFromDate($row->year, $row->month, 1)->translatedFormat('M Y'),
                ]);
        }

        $totals = [
            'invoice_count' => $data->sum('invoice_count'),
            'total_base' => round($data->sum('total_base'), 2),
            'total_vat' => round($data->sum('total_vat'), 2),
            'total_irpf' => round($data->sum('total_irpf'), 2),
            'total_amount' => round($data->sum('total_amount'), 2),
        ];

        return Inertia::render('Reports/Sales/ByPeriod', [
            'data' => $data,
            'totals' => $totals,
            'filters' => [
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
                'group_by' => $groupBy,
            ],
        ]);
    }

    public function exportSalesCsv(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $documents = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->with('client:id,legal_name,nif')
            ->orderBy('issue_date')
            ->orderBy('number')
            ->get();

        return new StreamedResponse(function () use ($documents) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Número', 'Tipo', 'Fecha', 'Cliente', 'NIF',
                'Base imponible', 'IVA', 'IRPF', 'Total', 'Estado',
            ], ';');

            foreach ($documents as $doc) {
                fputcsv($handle, [
                    $doc->number,
                    Document::documentTypeLabel($doc->document_type),
                    $doc->issue_date->format('d/m/Y'),
                    $doc->client?->legal_name ?? '',
                    $doc->client?->nif ?? '',
                    number_format((float) $doc->tax_base, 2, ',', ''),
                    number_format((float) $doc->total_vat, 2, ',', ''),
                    number_format((float) $doc->total_irpf, 2, ',', ''),
                    number_format((float) $doc->total, 2, ',', ''),
                    Document::statusLabel($doc->status),
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ventas_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ─── Fiscal Reports ───

    public function modelo303(Request $request)
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $quarter = (int) ($request->input('quarter') ?? ceil(now()->month / 3));

        [$dateFrom, $dateTo] = $this->quarterRange($year, $quarter);
        $company = CompanyProfile::first();

        // IVA Repercutido (sales VAT) — from issued invoices
        $vatIssued = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->join('document_lines', 'documents.id', '=', 'document_lines.document_id')
            ->select(
                'document_lines.vat_rate',
                DB::raw('SUM(document_lines.line_subtotal) as base'),
                DB::raw('SUM(document_lines.vat_amount) as vat'),
            )
            ->groupBy('document_lines.vat_rate')
            ->orderBy('document_lines.vat_rate')
            ->get();

        // IVA Soportado (purchase VAT) — from purchase invoices + expenses
        $vatReceivedDocs = Document::received()
            ->ofType(Document::TYPE_PURCHASE_INVOICE)
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->join('document_lines', 'documents.id', '=', 'document_lines.document_id')
            ->select(
                'document_lines.vat_rate',
                DB::raw('SUM(document_lines.line_subtotal) as base'),
                DB::raw('SUM(document_lines.vat_amount) as vat'),
            )
            ->groupBy('document_lines.vat_rate')
            ->orderBy('document_lines.vat_rate')
            ->get();

        $vatExpenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->select(
                'vat_rate',
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(vat_amount) as vat'),
            )
            ->groupBy('vat_rate')
            ->orderBy('vat_rate')
            ->get();

        // Merge purchase invoices + expenses VAT
        $vatReceived = $this->mergeVatBreakdowns($vatReceivedDocs, $vatExpenses);

        $totalVatIssued = round($vatIssued->sum('vat'), 2);
        $totalVatReceived = round(collect($vatReceived)->sum('vat'), 2);
        $difference = round($totalVatIssued - $totalVatReceived, 2);

        return Inertia::render('Reports/Fiscal/Modelo303', [
            'company' => $company,
            'vatIssued' => $vatIssued,
            'vatReceived' => $vatReceived,
            'summary' => [
                'total_vat_issued' => $totalVatIssued,
                'total_vat_received' => $totalVatReceived,
                'difference' => $difference,
            ],
            'filters' => [
                'year' => $year,
                'quarter' => $quarter,
            ],
        ]);
    }

    public function modelo130(Request $request)
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $quarter = (int) ($request->input('quarter') ?? ceil(now()->month / 3));

        [$dateFrom, $dateTo] = $this->quarterRange($year, $quarter);
        $company = CompanyProfile::first();

        // Ingresos (revenue) — issued invoices
        $revenue = (float) Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('tax_base');

        // Gastos deducibles — purchase invoices + expenses
        $purchaseBase = (float) Document::received()
            ->ofType(Document::TYPE_PURCHASE_INVOICE)
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('tax_base');

        $expenseBase = (float) Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->sum('subtotal');

        $deductibleExpenses = $purchaseBase + $expenseBase;

        // Rendimiento neto
        $netIncome = $revenue - $deductibleExpenses;

        // 20% del rendimiento neto (tipo general del 130)
        $irpfRate = 20;
        $irpfPayment = round($netIncome * $irpfRate / 100, 2);

        // Retenciones soportadas en trimestre (IRPF retenido por clientes)
        $retentions = (float) Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('total_irpf');

        // Previous quarters in same year
        $previousPayments = 0;
        for ($q = 1; $q < $quarter; $q++) {
            [$pFrom, $pTo] = $this->quarterRange($year, $q);

            $pRevenue = (float) Document::issued()
                ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
                ->where('status', '!=', Document::STATUS_DRAFT)
                ->whereBetween('issue_date', [$pFrom, $pTo])
                ->sum('tax_base');

            $pPurchaseBase = (float) Document::received()
                ->ofType(Document::TYPE_PURCHASE_INVOICE)
                ->where('status', '!=', Document::STATUS_DRAFT)
                ->whereBetween('issue_date', [$pFrom, $pTo])
                ->sum('tax_base');

            $pExpenseBase = (float) Expense::whereBetween('expense_date', [$pFrom, $pTo])
                ->sum('subtotal');

            $pNet = $pRevenue - ($pPurchaseBase + $pExpenseBase);
            $previousPayments += round($pNet * $irpfRate / 100, 2);
        }

        $toPay = max(0, $irpfPayment - $retentions - $previousPayments);

        return Inertia::render('Reports/Fiscal/Modelo130', [
            'company' => $company,
            'data' => [
                'revenue' => round($revenue, 2),
                'deductible_expenses' => round($deductibleExpenses, 2),
                'net_income' => round($netIncome, 2),
                'irpf_rate' => $irpfRate,
                'irpf_payment' => $irpfPayment,
                'retentions' => round($retentions, 2),
                'previous_payments' => round($previousPayments, 2),
                'to_pay' => round($toPay, 2),
            ],
            'filters' => [
                'year' => $year,
                'quarter' => $quarter,
            ],
        ]);
    }

    public function modelo390(Request $request)
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $company = CompanyProfile::first();

        $dateFrom = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $dateTo = Carbon::createFromDate($year, 12, 31)->endOfDay();

        // IVA Repercutido annual
        $vatIssued = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->join('document_lines', 'documents.id', '=', 'document_lines.document_id')
            ->select(
                'document_lines.vat_rate',
                DB::raw('SUM(document_lines.line_subtotal) as base'),
                DB::raw('SUM(document_lines.vat_amount) as vat'),
            )
            ->groupBy('document_lines.vat_rate')
            ->orderBy('document_lines.vat_rate')
            ->get();

        // IVA Soportado annual
        $vatReceivedDocs = Document::received()
            ->ofType(Document::TYPE_PURCHASE_INVOICE)
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->join('document_lines', 'documents.id', '=', 'document_lines.document_id')
            ->select(
                'document_lines.vat_rate',
                DB::raw('SUM(document_lines.line_subtotal) as base'),
                DB::raw('SUM(document_lines.vat_amount) as vat'),
            )
            ->groupBy('document_lines.vat_rate')
            ->orderBy('document_lines.vat_rate')
            ->get();

        $vatExpenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->select(
                'vat_rate',
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(vat_amount) as vat'),
            )
            ->groupBy('vat_rate')
            ->orderBy('vat_rate')
            ->get();

        $vatReceived = $this->mergeVatBreakdowns($vatReceivedDocs, $vatExpenses);

        // Quarterly breakdown
        $quarters = [];
        for ($q = 1; $q <= 4; $q++) {
            [$qFrom, $qTo] = $this->quarterRange($year, $q);

            $qVatIssued = (float) Document::issued()
                ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
                ->where('status', '!=', Document::STATUS_DRAFT)
                ->whereBetween('issue_date', [$qFrom, $qTo])
                ->sum('total_vat');

            $qVatReceived = (float) Document::received()
                ->ofType(Document::TYPE_PURCHASE_INVOICE)
                ->where('status', '!=', Document::STATUS_DRAFT)
                ->whereBetween('issue_date', [$qFrom, $qTo])
                ->sum('total_vat');

            $qVatExpenses = (float) Expense::whereBetween('expense_date', [$qFrom, $qTo])
                ->sum('vat_amount');

            $quarters[] = [
                'quarter' => $q,
                'vat_issued' => round($qVatIssued, 2),
                'vat_received' => round($qVatReceived + $qVatExpenses, 2),
                'difference' => round($qVatIssued - $qVatReceived - $qVatExpenses, 2),
            ];
        }

        $totalVatIssued = round($vatIssued->sum('vat'), 2);
        $totalVatReceived = round(collect($vatReceived)->sum('vat'), 2);

        return Inertia::render('Reports/Fiscal/Modelo390', [
            'company' => $company,
            'vatIssued' => $vatIssued,
            'vatReceived' => $vatReceived,
            'quarters' => $quarters,
            'summary' => [
                'total_vat_issued' => $totalVatIssued,
                'total_vat_received' => $totalVatReceived,
                'difference' => round($totalVatIssued - $totalVatReceived, 2),
            ],
            'filters' => [
                'year' => $year,
            ],
        ]);
    }

    // ─── Helpers ───

    private function resolveDateRange(Request $request): array
    {
        $dateFrom = $request->input('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfYear();

        $dateTo = $request->input('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        return [$dateFrom, $dateTo];
    }

    private function quarterRange(int $year, int $quarter): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $dateFrom = Carbon::createFromDate($year, $startMonth, 1)->startOfDay();
        $dateTo = $dateFrom->copy()->addMonths(3)->subDay()->endOfDay();

        return [$dateFrom, $dateTo];
    }

    private function mergeVatBreakdowns($docBreakdown, $expenseBreakdown): array
    {
        $merged = [];

        foreach ($docBreakdown as $row) {
            $rate = (string) $row->vat_rate;
            $merged[$rate] = [
                'vat_rate' => $row->vat_rate,
                'base' => (float) $row->base,
                'vat' => (float) $row->vat,
            ];
        }

        foreach ($expenseBreakdown as $row) {
            $rate = (string) $row->vat_rate;
            if (isset($merged[$rate])) {
                $merged[$rate]['base'] += (float) $row->base;
                $merged[$rate]['vat'] += (float) $row->vat;
            } else {
                $merged[$rate] = [
                    'vat_rate' => $row->vat_rate,
                    'base' => (float) $row->base,
                    'vat' => (float) $row->vat,
                ];
            }
        }

        // Round values
        foreach ($merged as &$entry) {
            $entry['base'] = round($entry['base'], 2);
            $entry['vat'] = round($entry['vat'], 2);
        }

        return array_values($merged);
    }
}
