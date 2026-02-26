<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\Client;
use App\Models\DocumentLine;
use App\Models\Expense;
use App\Services\ReportPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportPdfService $reportPdfService,
    ) {}

    // ─── Sales Reports ───

    public function salesByClient(Request $request)
    {
        return Inertia::render('Reports/Sales/ByClient', $this->getSalesByClientData($request));
    }

    public function salesByProduct(Request $request)
    {
        return Inertia::render('Reports/Sales/ByProduct', $this->getSalesByProductData($request));
    }

    public function salesByPeriod(Request $request)
    {
        return Inertia::render('Reports/Sales/ByPeriod', $this->getSalesByPeriodData($request));
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

    // ─── Sales PDF Exports ───

    public function exportSalesByClientPdf(Request $request)
    {
        $data = $this->getSalesByClientData($request);

        return $this->reportPdfService->download(
            'pdf.reports.sales-by-client',
            $data,
            'ventas_por_cliente_' . now()->format('Y-m-d') . '.pdf',
        );
    }

    public function exportSalesByProductPdf(Request $request)
    {
        $data = $this->getSalesByProductData($request);

        return $this->reportPdfService->download(
            'pdf.reports.sales-by-product',
            $data,
            'ventas_por_producto_' . now()->format('Y-m-d') . '.pdf',
        );
    }

    public function exportSalesByPeriodPdf(Request $request)
    {
        $data = $this->getSalesByPeriodData($request);

        return $this->reportPdfService->download(
            'pdf.reports.sales-by-period',
            $data,
            'ventas_por_periodo_' . now()->format('Y-m-d') . '.pdf',
        );
    }

    // ─── Fiscal Reports ───

    public function modelo303(Request $request)
    {
        return Inertia::render('Reports/Fiscal/Modelo303', $this->getModelo303Data($request));
    }

    public function modelo130(Request $request)
    {
        return Inertia::render('Reports/Fiscal/Modelo130', $this->getModelo130Data($request));
    }

    public function modelo390(Request $request)
    {
        return Inertia::render('Reports/Fiscal/Modelo390', $this->getModelo390Data($request));
    }

    // ─── Fiscal PDF Exports ───

    public function exportModelo303Pdf(Request $request)
    {
        $data = $this->getModelo303Data($request);

        return $this->reportPdfService->download(
            'pdf.reports.modelo-303',
            $data,
            'modelo_303_' . $data['filters']['year'] . '_' . $data['filters']['quarter'] . 'T.pdf',
        );
    }

    public function exportModelo130Pdf(Request $request)
    {
        $data = $this->getModelo130Data($request);

        return $this->reportPdfService->download(
            'pdf.reports.modelo-130',
            $data,
            'modelo_130_' . $data['filters']['year'] . '_' . $data['filters']['quarter'] . 'T.pdf',
        );
    }

    public function exportModelo390Pdf(Request $request)
    {
        $data = $this->getModelo390Data($request);

        return $this->reportPdfService->download(
            'pdf.reports.modelo-390',
            $data,
            'modelo_390_' . $data['filters']['year'] . '.pdf',
        );
    }

    // ─── Fiscal CSV Exports ───

    public function exportModelo303Csv(Request $request)
    {
        $data = $this->getModelo303Data($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // IVA Devengado
            fputcsv($handle, [__('reports.vat_charged')], ';');
            fputcsv($handle, [__('reports.vat_type'), __('reports.col_base'), __('reports.col_amount')], ';');
            foreach ($data['vatIssued'] as $row) {
                fputcsv($handle, [
                    $row->vat_rate . '%',
                    number_format((float) $row->base, 2, ',', ''),
                    number_format((float) $row->vat, 2, ',', ''),
                ], ';');
            }
            fputcsv($handle, [__('reports.total_vat_charged'), '', number_format($data['summary']['total_vat_issued'], 2, ',', '')], ';');
            fputcsv($handle, [], ';');

            // IVA Deducible
            fputcsv($handle, [__('reports.vat_deductible')], ';');
            fputcsv($handle, [__('reports.vat_type'), __('reports.col_base'), __('reports.col_amount')], ';');
            foreach ($data['vatReceived'] as $row) {
                fputcsv($handle, [
                    $row['vat_rate'] . '%',
                    number_format((float) $row['base'], 2, ',', ''),
                    number_format((float) $row['vat'], 2, ',', ''),
                ], ';');
            }
            fputcsv($handle, [__('reports.total_vat_deductible'), '', number_format($data['summary']['total_vat_received'], 2, ',', '')], ';');
            fputcsv($handle, [], ';');

            // Result
            fputcsv($handle, [__('reports.difference'), '', number_format($data['summary']['difference'], 2, ',', '')], ';');

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_303_' . $data['filters']['year'] . '_' . $data['filters']['quarter'] . 'T.csv"',
        ]);
    }

    public function exportModelo130Csv(Request $request)
    {
        $data = $this->getModelo130Data($request);
        $d = $data['data'];

        return new StreamedResponse(function () use ($d) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [__('reports.section_direct_estimation')], ';');
            fputcsv($handle, [__('reports.row_01_income'), number_format($d['revenue'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.row_02_expenses'), number_format($d['deductible_expenses'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.row_03_net'), number_format($d['net_income'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.row_04_pct', ['rate' => $d['irpf_rate']]), number_format($d['irpf_payment'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.row_05_withholdings'), number_format($d['retentions'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.row_06_previous'), number_format($d['previous_payments'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.row_07_total'), number_format($d['to_pay'], 2, ',', '')], ';');

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_130_' . $data['filters']['year'] . '_' . $data['filters']['quarter'] . 'T.csv"',
        ]);
    }

    public function exportModelo390Csv(Request $request)
    {
        $data = $this->getModelo390Data($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // IVA Devengado
            fputcsv($handle, [__('reports.annual_vat_charged')], ';');
            fputcsv($handle, [__('reports.vat_type'), __('reports.col_vat_base'), __('reports.col_vat_amount')], ';');
            foreach ($data['vatIssued'] as $row) {
                fputcsv($handle, [
                    $row->vat_rate . '%',
                    number_format((float) $row->base, 2, ',', ''),
                    number_format((float) $row->vat, 2, ',', ''),
                ], ';');
            }
            fputcsv($handle, [__('reports.total_vat_charged'), '', number_format($data['summary']['total_vat_issued'], 2, ',', '')], ';');
            fputcsv($handle, [], ';');

            // IVA Deducible
            fputcsv($handle, [__('reports.annual_vat_deductible')], ';');
            fputcsv($handle, [__('reports.vat_type'), __('reports.col_vat_base'), __('reports.col_vat_amount')], ';');
            foreach ($data['vatReceived'] as $row) {
                fputcsv($handle, [
                    $row['vat_rate'] . '%',
                    number_format((float) $row['base'], 2, ',', ''),
                    number_format((float) $row['vat'], 2, ',', ''),
                ], ';');
            }
            fputcsv($handle, [__('reports.total_vat_deductible'), '', number_format($data['summary']['total_vat_received'], 2, ',', '')], ';');
            fputcsv($handle, [], ';');

            // Quarterly breakdown
            fputcsv($handle, [__('reports.quarterly_breakdown')], ';');
            fputcsv($handle, [__('reports.col_quarter'), __('reports.col_vat_charged'), __('reports.col_vat_deductible_short'), __('reports.col_difference')], ';');
            foreach ($data['quarters'] as $q) {
                fputcsv($handle, [
                    $q['quarter'] . 'T',
                    number_format($q['vat_issued'], 2, ',', ''),
                    number_format($q['vat_received'], 2, ',', ''),
                    number_format($q['difference'], 2, ',', ''),
                ], ';');
            }
            fputcsv($handle, [], ';');

            // Result
            fputcsv($handle, [__('reports.annual_result'), '', '', number_format($data['summary']['difference'], 2, ',', '')], ';');

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_390_' . $data['filters']['year'] . '.csv"',
        ]);
    }

    // ─── Data Helpers ───

    private function getSalesByClientData(Request $request): array
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

        return [
            'data' => $data,
            'totals' => $totals,
            'filters' => [
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ],
        ];
    }

    private function getSalesByProductData(Request $request): array
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

        return [
            'data' => $data,
            'totals' => $totals,
            'filters' => [
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ],
        ];
    }

    private function getSalesByPeriodData(Request $request): array
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $groupBy = $request->input('group_by', 'month');

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

        return [
            'data' => $data,
            'totals' => $totals,
            'filters' => [
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
                'group_by' => $groupBy,
            ],
        ];
    }

    private function getModelo303Data(Request $request): array
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $quarter = (int) ($request->input('quarter') ?? ceil(now()->month / 3));

        [$dateFrom, $dateTo] = $this->quarterRange($year, $quarter);
        $company = CompanyProfile::first();

        // IVA Repercutido (sales VAT)
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

        // IVA Soportado (purchase VAT)
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

        // Exempt operations (casilla 13)
        $exemptBase = (float) DocumentLine::whereHas('document', function ($q) use ($dateFrom, $dateTo) {
            $q->issued()
              ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
              ->where('status', '!=', Document::STATUS_DRAFT)
              ->whereBetween('issue_date', [$dateFrom, $dateTo]);
        })
            ->whereNotNull('exemption_code')
            ->sum('line_subtotal');

        // Surcharge (recargo de equivalencia)
        $totalSurcharge = (float) Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('total_surcharge');

        $totalVatIssued = round($vatIssued->sum('vat'), 2);
        $totalVatReceived = round(collect($vatReceived)->sum('vat'), 2);
        $difference = round($totalVatIssued + round($totalSurcharge, 2) - $totalVatReceived, 2);

        return [
            'company' => $company,
            'vatIssued' => $vatIssued,
            'vatReceived' => $vatReceived,
            'exemptBase' => round($exemptBase, 2),
            'totalSurcharge' => round($totalSurcharge, 2),
            'summary' => [
                'total_vat_issued' => $totalVatIssued,
                'total_vat_received' => $totalVatReceived,
                'total_surcharge' => round($totalSurcharge, 2),
                'difference' => $difference,
            ],
            'filters' => [
                'year' => $year,
                'quarter' => $quarter,
            ],
        ];
    }

    private function getModelo130Data(Request $request): array
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $quarter = (int) ($request->input('quarter') ?? ceil(now()->month / 3));

        [$dateFrom, $dateTo] = $this->quarterRange($year, $quarter);
        $company = CompanyProfile::first();

        // Ingresos
        $revenue = (float) Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('tax_base');

        // Gastos deducibles
        $purchaseBase = (float) Document::received()
            ->ofType(Document::TYPE_PURCHASE_INVOICE)
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('tax_base');

        $expenseBase = (float) Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->sum('subtotal');

        $deductibleExpenses = $purchaseBase + $expenseBase;
        $netIncome = $revenue - $deductibleExpenses;
        $irpfRate = 20;
        $irpfPayment = round($netIncome * $irpfRate / 100, 2);

        // Retenciones
        $retentions = (float) Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('total_irpf');

        // Previous quarters
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

        return [
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
        ];
    }

    private function getModelo390Data(Request $request): array
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

        return [
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
        ];
    }

    // ─── Utility Helpers ───

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

        foreach ($merged as &$entry) {
            $entry['base'] = round($entry['base'], 2);
            $entry['vat'] = round($entry['vat'], 2);
        }

        return array_values($merged);
    }
}
