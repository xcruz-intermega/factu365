<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\Expense;
use App\Services\ReportPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FiscalDeclarationController extends Controller
{
    public function __construct(
        private ReportPdfService $reportPdfService,
    ) {}

    // ─── Modelo 111 ───

    public function modelo111(Request $request)
    {
        return Inertia::render('Reports/Fiscal/Modelo111', $this->getModelo111Data($request));
    }

    public function exportModelo111Pdf(Request $request)
    {
        $data = $this->getModelo111Data($request);

        return $this->reportPdfService->download(
            'pdf.reports.modelo-111',
            $data,
            'modelo_111_' . $data['filters']['year'] . '_' . $data['filters']['quarter'] . 'T.pdf',
        );
    }

    public function exportModelo111Csv(Request $request)
    {
        $data = $this->getModelo111Data($request);
        $d = $data['data'];

        return new StreamedResponse(function () use ($d) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [__('reports.section_professional')], ';');
            fputcsv($handle, [__('reports.casilla_04_recipients'), $d['recipients']], ';');
            fputcsv($handle, [__('reports.casilla_05_base'), number_format($d['base'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.casilla_06_withheld'), number_format($d['withheld'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.result_to_pay'), number_format($d['to_pay'], 2, ',', '')], ';');

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_111_' . $data['filters']['year'] . '_' . $data['filters']['quarter'] . 'T.csv"',
        ]);
    }

    // ─── Modelo 115 ───

    public function modelo115(Request $request)
    {
        return Inertia::render('Reports/Fiscal/Modelo115', $this->getModelo115Data($request));
    }

    public function exportModelo115Pdf(Request $request)
    {
        $data = $this->getModelo115Data($request);

        return $this->reportPdfService->download(
            'pdf.reports.modelo-115',
            $data,
            'modelo_115_' . $data['filters']['year'] . '_' . $data['filters']['quarter'] . 'T.pdf',
        );
    }

    public function exportModelo115Csv(Request $request)
    {
        $data = $this->getModelo115Data($request);
        $d = $data['data'];

        return new StreamedResponse(function () use ($d) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [__('reports.section_rental')], ';');
            fputcsv($handle, [__('reports.casilla_01_landlords'), $d['landlords']], ';');
            fputcsv($handle, [__('reports.casilla_02_base'), number_format($d['base'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.casilla_03_withheld'), number_format($d['withheld'], 2, ',', '')], ';');
            fputcsv($handle, [__('reports.result_to_pay'), number_format($d['to_pay'], 2, ',', '')], ';');

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_115_' . $data['filters']['year'] . '_' . $data['filters']['quarter'] . 'T.csv"',
        ]);
    }

    // ─── Modelo 347 ───

    public function modelo347(Request $request)
    {
        return Inertia::render('Reports/Fiscal/Modelo347', $this->getModelo347Data($request));
    }

    public function exportModelo347Pdf(Request $request)
    {
        $data = $this->getModelo347Data($request);

        return $this->reportPdfService->download(
            'pdf.reports.modelo-347',
            $data,
            'modelo_347_' . $data['filters']['year'] . '.pdf',
            'landscape',
        );
    }

    public function exportModelo347Csv(Request $request)
    {
        $data = $this->getModelo347Data($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // Sales section
            fputcsv($handle, [__('reports.section_sales')], ';');
            fputcsv($handle, [
                __('reports.col_nif'), __('reports.col_client'),
                __('reports.col_annual_total'),
                __('reports.col_q1'), __('reports.col_q2'), __('reports.col_q3'), __('reports.col_q4'),
            ], ';');
            foreach ($data['sales'] as $row) {
                fputcsv($handle, [
                    $row['nif'],
                    $row['name'],
                    number_format($row['annual_total'], 2, ',', ''),
                    number_format($row['q1'], 2, ',', ''),
                    number_format($row['q2'], 2, ',', ''),
                    number_format($row['q3'], 2, ',', ''),
                    number_format($row['q4'], 2, ',', ''),
                ], ';');
            }
            fputcsv($handle, [], ';');

            // Purchases section
            fputcsv($handle, [__('reports.section_purchases')], ';');
            fputcsv($handle, [
                __('reports.col_nif'), __('books.col_supplier'),
                __('reports.col_annual_total'),
                __('reports.col_q1'), __('reports.col_q2'), __('reports.col_q3'), __('reports.col_q4'),
            ], ';');
            foreach ($data['purchases'] as $row) {
                fputcsv($handle, [
                    $row['nif'],
                    $row['name'],
                    number_format($row['annual_total'], 2, ',', ''),
                    number_format($row['q1'], 2, ',', ''),
                    number_format($row['q2'], 2, ',', ''),
                    number_format($row['q3'], 2, ',', ''),
                    number_format($row['q4'], 2, ',', ''),
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_347_' . $data['filters']['year'] . '.csv"',
        ]);
    }

    // ─── Data Helpers ───

    private function getModelo111Data(Request $request): array
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $quarter = (int) ($request->input('quarter') ?? ceil(now()->month / 3));

        [$dateFrom, $dateTo] = $this->quarterRange($year, $quarter);
        $company = CompanyProfile::first();

        // Professional IRPF from purchase invoices
        $docIrpf = Document::received()
            ->ofType(Document::TYPE_PURCHASE_INVOICE)
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->where('total_irpf', '>', 0)
            ->select(
                DB::raw('COUNT(DISTINCT client_id) as recipients'),
                DB::raw('SUM(tax_base) as base'),
                DB::raw('SUM(total_irpf) as withheld'),
            )
            ->first();

        // Professional IRPF from expenses
        $expIrpf = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->where('irpf_type', 'professional')
            ->where('irpf_rate', '>', 0)
            ->select(
                DB::raw('COUNT(DISTINCT COALESCE(supplier_client_id, supplier_name)) as recipients'),
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(irpf_amount) as withheld'),
            )
            ->first();

        $recipients = ($docIrpf->recipients ?? 0) + ($expIrpf->recipients ?? 0);
        $base = round(($docIrpf->base ?? 0) + ($expIrpf->base ?? 0), 2);
        $withheld = round(($docIrpf->withheld ?? 0) + ($expIrpf->withheld ?? 0), 2);

        return [
            'company' => $company,
            'data' => [
                'recipients' => $recipients,
                'base' => $base,
                'withheld' => $withheld,
                'to_pay' => $withheld,
            ],
            'filters' => [
                'year' => $year,
                'quarter' => $quarter,
            ],
        ];
    }

    private function getModelo115Data(Request $request): array
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $quarter = (int) ($request->input('quarter') ?? ceil(now()->month / 3));

        [$dateFrom, $dateTo] = $this->quarterRange($year, $quarter);
        $company = CompanyProfile::first();

        // Rental IRPF from expenses only
        $data = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->where('irpf_type', 'rental')
            ->where('irpf_rate', '>', 0)
            ->select(
                DB::raw('COUNT(DISTINCT COALESCE(supplier_client_id, supplier_name)) as landlords'),
                DB::raw('SUM(subtotal) as base'),
                DB::raw('SUM(irpf_amount) as withheld'),
            )
            ->first();

        return [
            'company' => $company,
            'data' => [
                'landlords' => $data->landlords ?? 0,
                'base' => round($data->base ?? 0, 2),
                'withheld' => round($data->withheld ?? 0, 2),
                'to_pay' => round($data->withheld ?? 0, 2),
            ],
            'filters' => [
                'year' => $year,
                'quarter' => $quarter,
            ],
        ];
    }

    private function getModelo347Data(Request $request): array
    {
        $year = (int) ($request->input('year') ?? now()->year);
        $company = CompanyProfile::first();

        $dateFrom = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $dateTo = Carbon::createFromDate($year, 12, 31)->endOfDay();
        $threshold = 3005.06;

        // Section A: Sales by client NIF
        $salesRaw = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->whereHas('client', fn ($q) => $q->whereNotNull('nif')->where('nif', '!=', ''))
            ->join('clients', 'documents.client_id', '=', 'clients.id')
            ->select(
                'clients.nif',
                'clients.legal_name as name',
                DB::raw('QUARTER(issue_date) as quarter'),
                DB::raw('SUM(total) as quarter_total'),
            )
            ->groupBy('clients.nif', 'clients.legal_name', DB::raw('QUARTER(issue_date)'))
            ->get();

        $sales = $this->aggregateByNif($salesRaw, $threshold);

        // Section B: Purchases by supplier NIF
        $purchaseDocsRaw = Document::received()
            ->ofType(Document::TYPE_PURCHASE_INVOICE)
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->whereHas('client', fn ($q) => $q->whereNotNull('nif')->where('nif', '!=', ''))
            ->join('clients', 'documents.client_id', '=', 'clients.id')
            ->select(
                'clients.nif',
                'clients.legal_name as name',
                DB::raw('QUARTER(issue_date) as quarter'),
                DB::raw('SUM(total) as quarter_total'),
            )
            ->groupBy('clients.nif', 'clients.legal_name', DB::raw('QUARTER(issue_date)'))
            ->get();

        $expensesRaw = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->whereHas('supplier', fn ($q) => $q->whereNotNull('nif')->where('nif', '!=', ''))
            ->join('clients', 'expenses.supplier_client_id', '=', 'clients.id')
            ->select(
                'clients.nif',
                'clients.legal_name as name',
                DB::raw('QUARTER(expense_date) as quarter'),
                DB::raw('SUM(expenses.total) as quarter_total'),
            )
            ->groupBy('clients.nif', 'clients.legal_name', DB::raw('QUARTER(expense_date)'))
            ->get();

        $purchasesAll = $purchaseDocsRaw->merge($expensesRaw);
        $purchases = $this->aggregateByNif($purchasesAll, $threshold);

        // Check if there are expenses without NIF
        $missingNifCount = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->whereNull('supplier_client_id')
            ->count();

        return [
            'company' => $company,
            'sales' => $sales,
            'purchases' => $purchases,
            'hasMissingNif' => $missingNifCount > 0,
            'filters' => [
                'year' => $year,
            ],
        ];
    }

    private function aggregateByNif($rows, float $threshold): array
    {
        $byNif = [];
        foreach ($rows as $row) {
            $nif = $row->nif;
            if (!isset($byNif[$nif])) {
                $byNif[$nif] = [
                    'nif' => $nif,
                    'name' => $row->name,
                    'q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0,
                    'annual_total' => 0,
                ];
            }
            $q = 'q' . $row->quarter;
            $byNif[$nif][$q] += (float) $row->quarter_total;
            $byNif[$nif]['annual_total'] += (float) $row->quarter_total;
        }

        // Filter by threshold and round
        return collect($byNif)
            ->filter(fn ($r) => abs($r['annual_total']) >= $threshold)
            ->map(fn ($r) => [
                ...$r,
                'q1' => round($r['q1'], 2),
                'q2' => round($r['q2'], 2),
                'q3' => round($r['q3'], 2),
                'q4' => round($r['q4'], 2),
                'annual_total' => round($r['annual_total'], 2),
            ])
            ->sortByDesc('annual_total')
            ->values()
            ->toArray();
    }

    private function quarterRange(int $year, int $quarter): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $dateFrom = Carbon::createFromDate($year, $startMonth, 1)->startOfDay();
        $dateTo = $dateFrom->copy()->addMonths(3)->subDay()->endOfDay();

        return [$dateFrom, $dateTo];
    }
}
