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

class RegisterBookController extends Controller
{
    public function __construct(
        private ReportPdfService $reportPdfService,
    ) {}

    // ─── Libro Ventas ───

    public function libroVentas(Request $request)
    {
        return Inertia::render('Reports/Books/LibroVentas', $this->getLibroVentasData($request));
    }

    public function exportLibroVentasPdf(Request $request)
    {
        $data = $this->getLibroVentasData($request);

        return $this->reportPdfService->download(
            'pdf.reports.libro-ventas',
            $data,
            'libro_ventas_' . now()->format('Y-m-d') . '.pdf',
            'landscape',
        );
    }

    public function exportLibroVentasCsv(Request $request)
    {
        $data = $this->getLibroVentasData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                __('books.col_date'), __('books.col_number'), __('books.col_series'),
                __('books.col_client'), __('books.col_nif'),
                __('books.col_base'), __('books.col_vat'), __('books.col_irpf'), __('books.col_total'),
            ], ';');

            foreach ($data['data'] as $row) {
                fputcsv($handle, [
                    $row['issue_date'],
                    $row['number'],
                    $row['series_name'] ?? '',
                    $row['client_name'],
                    $row['client_nif'],
                    number_format((float) $row['tax_base'], 2, ',', ''),
                    number_format((float) $row['total_vat'], 2, ',', ''),
                    number_format((float) $row['total_irpf'], 2, ',', ''),
                    number_format((float) $row['total'], 2, ',', ''),
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="libro_ventas_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ─── Libro Compras ───

    public function libroCompras(Request $request)
    {
        return Inertia::render('Reports/Books/LibroCompras', $this->getLibroComprasData($request));
    }

    public function exportLibroComprasPdf(Request $request)
    {
        $data = $this->getLibroComprasData($request);

        return $this->reportPdfService->download(
            'pdf.reports.libro-compras',
            $data,
            'libro_compras_' . now()->format('Y-m-d') . '.pdf',
            'landscape',
        );
    }

    public function exportLibroComprasCsv(Request $request)
    {
        $data = $this->getLibroComprasData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                __('books.col_date'), __('books.col_invoice_number'), __('books.col_supplier'),
                __('books.col_nif'), __('books.col_base'), __('books.col_vat'),
                __('books.col_irpf'), __('books.col_total'), __('books.col_origin'),
            ], ';');

            foreach ($data['data'] as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['invoice_number'],
                    $row['supplier_name'],
                    $row['supplier_nif'],
                    number_format((float) $row['tax_base'], 2, ',', ''),
                    number_format((float) $row['total_vat'], 2, ',', ''),
                    number_format((float) $row['total_irpf'], 2, ',', ''),
                    number_format((float) $row['total'], 2, ',', ''),
                    $row['origin'],
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="libro_compras_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ─── Libro Facturas Expedidas ───

    public function libroExpedidas(Request $request)
    {
        return Inertia::render('Reports/Books/LibroExpedidas', $this->getLibroExpedidasData($request));
    }

    public function exportLibroExpedidasPdf(Request $request)
    {
        $data = $this->getLibroExpedidasData($request);

        return $this->reportPdfService->download(
            'pdf.reports.libro-expedidas',
            $data,
            'libro_expedidas_' . now()->format('Y-m-d') . '.pdf',
            'landscape',
        );
    }

    public function exportLibroExpedidasCsv(Request $request)
    {
        $data = $this->getLibroExpedidasData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                __('books.col_series'), __('books.col_number'), __('books.col_date'),
                __('books.col_nif'), __('books.col_name'), __('books.col_invoice_type'),
                __('books.col_base'), __('books.col_vat'), __('books.col_surcharge'), __('books.col_total'),
            ], ';');

            foreach ($data['data'] as $row) {
                fputcsv($handle, [
                    $row['series_name'] ?? '',
                    $row['number'],
                    $row['issue_date'],
                    $row['client_nif'],
                    $row['client_name'],
                    $row['invoice_type_label'],
                    number_format((float) $row['tax_base'], 2, ',', ''),
                    number_format((float) $row['total_vat'], 2, ',', ''),
                    number_format((float) $row['total_surcharge'], 2, ',', ''),
                    number_format((float) $row['total'], 2, ',', ''),
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="libro_expedidas_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ─── Libro Facturas Recibidas ───

    public function libroRecibidas(Request $request)
    {
        return Inertia::render('Reports/Books/LibroRecibidas', $this->getLibroRecibidasData($request));
    }

    public function exportLibroRecibidasPdf(Request $request)
    {
        $data = $this->getLibroRecibidasData($request);

        return $this->reportPdfService->download(
            'pdf.reports.libro-recibidas',
            $data,
            'libro_recibidas_' . now()->format('Y-m-d') . '.pdf',
            'landscape',
        );
    }

    public function exportLibroRecibidasCsv(Request $request)
    {
        $data = $this->getLibroRecibidasData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                __('books.col_invoice_number'), __('books.col_date'),
                __('books.col_nif'), __('books.col_name'),
                __('books.col_base'), __('books.col_vat'), __('books.col_total'), __('books.col_origin'),
            ], ';');

            foreach ($data['data'] as $row) {
                fputcsv($handle, [
                    $row['invoice_number'],
                    $row['date'],
                    $row['supplier_nif'],
                    $row['supplier_name'],
                    number_format((float) $row['tax_base'], 2, ',', ''),
                    number_format((float) $row['total_vat'], 2, ',', ''),
                    number_format((float) $row['total'], 2, ',', ''),
                    $row['origin'],
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="libro_recibidas_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ─── Data Helpers ───

    private function getLibroVentasData(Request $request): array
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $documents = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->with(['client:id,legal_name,trade_name,nif', 'series:id,prefix'])
            ->orderBy('issue_date')
            ->orderBy('number')
            ->get();

        $data = $documents->map(fn ($doc) => [
            'id' => $doc->id,
            'issue_date' => $doc->issue_date->format('d/m/Y'),
            'issue_date_raw' => $doc->issue_date->format('Y-m-d'),
            'month_key' => $doc->issue_date->format('Y-m'),
            'month_label' => $doc->issue_date->translatedFormat('F Y'),
            'number' => $doc->number,
            'series_name' => $doc->series?->prefix,
            'client_name' => $doc->client?->trade_name ?: ($doc->client?->legal_name ?? ''),
            'client_nif' => $doc->client?->nif ?? '',
            'tax_base' => (float) $doc->tax_base,
            'total_vat' => (float) $doc->total_vat,
            'total_irpf' => (float) $doc->total_irpf,
            'total' => (float) $doc->total,
        ])->values()->toArray();

        $totals = [
            'tax_base' => round(array_sum(array_column($data, 'tax_base')), 2),
            'total_vat' => round(array_sum(array_column($data, 'total_vat')), 2),
            'total_irpf' => round(array_sum(array_column($data, 'total_irpf')), 2),
            'total' => round(array_sum(array_column($data, 'total')), 2),
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

    private function getLibroComprasData(Request $request): array
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        // Purchase invoices (documents)
        $purchaseDocs = Document::received()
            ->ofType(Document::TYPE_PURCHASE_INVOICE)
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->with(['client:id,legal_name,trade_name,nif'])
            ->orderBy('issue_date')
            ->orderBy('number')
            ->get()
            ->map(fn ($doc) => [
                'date' => $doc->issue_date->format('d/m/Y'),
                'date_raw' => $doc->issue_date->format('Y-m-d'),
                'month_key' => $doc->issue_date->format('Y-m'),
                'month_label' => $doc->issue_date->translatedFormat('F Y'),
                'invoice_number' => $doc->number,
                'supplier_name' => $doc->client?->trade_name ?: ($doc->client?->legal_name ?? ''),
                'supplier_nif' => $doc->client?->nif ?? '',
                'tax_base' => (float) $doc->tax_base,
                'total_vat' => (float) $doc->total_vat,
                'total_irpf' => (float) $doc->total_irpf,
                'total' => (float) $doc->total,
                'origin' => __('books.origin_document'),
                'origin_key' => 'document',
            ]);

        // Expenses
        $expenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->with(['supplier:id,legal_name,trade_name,nif'])
            ->orderBy('expense_date')
            ->get()
            ->map(fn ($exp) => [
                'date' => $exp->expense_date->format('d/m/Y'),
                'date_raw' => $exp->expense_date->format('Y-m-d'),
                'month_key' => $exp->expense_date->format('Y-m'),
                'month_label' => $exp->expense_date->translatedFormat('F Y'),
                'invoice_number' => $exp->invoice_number ?? $exp->concept,
                'supplier_name' => $exp->supplier?->trade_name ?: ($exp->supplier?->legal_name ?? ($exp->supplier_name ?? '')),
                'supplier_nif' => $exp->supplier?->nif ?? '',
                'tax_base' => (float) $exp->subtotal,
                'total_vat' => (float) $exp->vat_amount,
                'total_irpf' => (float) $exp->irpf_amount,
                'total' => (float) $exp->total,
                'origin' => __('books.origin_expense'),
                'origin_key' => 'expense',
            ]);

        $data = $purchaseDocs->merge($expenses)
            ->sortBy('date_raw')
            ->values()
            ->toArray();

        $totals = [
            'tax_base' => round(array_sum(array_column($data, 'tax_base')), 2),
            'total_vat' => round(array_sum(array_column($data, 'total_vat')), 2),
            'total_irpf' => round(array_sum(array_column($data, 'total_irpf')), 2),
            'total' => round(array_sum(array_column($data, 'total')), 2),
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

    private function getLibroExpedidasData(Request $request): array
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $documents = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->with(['client:id,legal_name,trade_name,nif', 'series:id,prefix', 'lines'])
            ->orderBy('issue_date')
            ->orderBy('number')
            ->get();

        $data = $documents->map(function ($doc) {
            $vatBreakdown = $doc->lines->groupBy('vat_rate')->map(fn ($lines, $rate) => [
                'vat_rate' => (float) $rate,
                'base' => round($lines->sum('line_subtotal'), 2),
                'vat' => round($lines->sum('vat_amount'), 2),
            ])->sortKeys()->values()->toArray();

            $invoiceType = $doc->document_type === Document::TYPE_RECTIFICATIVE ? 'R1' : 'F1';

            return [
                'id' => $doc->id,
                'issue_date' => $doc->issue_date->format('d/m/Y'),
                'issue_date_raw' => $doc->issue_date->format('Y-m-d'),
                'month_key' => $doc->issue_date->format('Y-m'),
                'month_label' => $doc->issue_date->translatedFormat('F Y'),
                'number' => $doc->number,
                'series_name' => $doc->series?->prefix,
                'client_name' => $doc->client?->trade_name ?: ($doc->client?->legal_name ?? ''),
                'client_nif' => $doc->client?->nif ?? '',
                'invoice_type' => $invoiceType,
                'invoice_type_label' => __("books.invoice_type_{$invoiceType}"),
                'tax_base' => (float) $doc->tax_base,
                'total_vat' => (float) $doc->total_vat,
                'total_surcharge' => (float) $doc->total_surcharge,
                'total' => (float) $doc->total,
                'vat_breakdown' => $vatBreakdown,
            ];
        })->values()->toArray();

        $totals = [
            'tax_base' => round(array_sum(array_column($data, 'tax_base')), 2),
            'total_vat' => round(array_sum(array_column($data, 'total_vat')), 2),
            'total_surcharge' => round(array_sum(array_column($data, 'total_surcharge')), 2),
            'total' => round(array_sum(array_column($data, 'total')), 2),
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

    private function getLibroRecibidasData(Request $request): array
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        // Purchase invoices with VAT breakdown
        $purchaseDocs = Document::received()
            ->ofType(Document::TYPE_PURCHASE_INVOICE)
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->with(['client:id,legal_name,trade_name,nif', 'lines'])
            ->orderBy('issue_date')
            ->orderBy('number')
            ->get()
            ->map(function ($doc) {
                $vatBreakdown = $doc->lines->groupBy('vat_rate')->map(fn ($lines, $rate) => [
                    'vat_rate' => (float) $rate,
                    'base' => round($lines->sum('line_subtotal'), 2),
                    'vat' => round($lines->sum('vat_amount'), 2),
                ])->sortKeys()->values()->toArray();

                return [
                    'date' => $doc->issue_date->format('d/m/Y'),
                    'date_raw' => $doc->issue_date->format('Y-m-d'),
                    'month_key' => $doc->issue_date->format('Y-m'),
                    'month_label' => $doc->issue_date->translatedFormat('F Y'),
                    'invoice_number' => $doc->number,
                    'supplier_name' => $doc->client?->trade_name ?: ($doc->client?->legal_name ?? ''),
                    'supplier_nif' => $doc->client?->nif ?? '',
                    'tax_base' => (float) $doc->tax_base,
                    'total_vat' => (float) $doc->total_vat,
                    'total' => (float) $doc->total,
                    'origin' => __('books.origin_document'),
                    'origin_key' => 'document',
                    'vat_breakdown' => $vatBreakdown,
                ];
            });

        // Expenses (single VAT rate each)
        $expenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->with(['supplier:id,legal_name,trade_name,nif'])
            ->orderBy('expense_date')
            ->get()
            ->map(function ($exp) {
                $vatBreakdown = [];
                if ((float) $exp->vat_rate > 0 || (float) $exp->subtotal > 0) {
                    $vatBreakdown[] = [
                        'vat_rate' => (float) $exp->vat_rate,
                        'base' => (float) $exp->subtotal,
                        'vat' => (float) $exp->vat_amount,
                    ];
                }

                return [
                    'date' => $exp->expense_date->format('d/m/Y'),
                    'date_raw' => $exp->expense_date->format('Y-m-d'),
                    'month_key' => $exp->expense_date->format('Y-m'),
                    'month_label' => $exp->expense_date->translatedFormat('F Y'),
                    'invoice_number' => $exp->invoice_number ?? $exp->concept,
                    'supplier_name' => $exp->supplier?->trade_name ?: ($exp->supplier?->legal_name ?? ($exp->supplier_name ?? '')),
                    'supplier_nif' => $exp->supplier?->nif ?? '',
                    'tax_base' => (float) $exp->subtotal,
                    'total_vat' => (float) $exp->vat_amount,
                    'total' => (float) $exp->total,
                    'origin' => __('books.origin_expense'),
                    'origin_key' => 'expense',
                    'vat_breakdown' => $vatBreakdown,
                ];
            });

        $data = $purchaseDocs->merge($expenses)
            ->sortBy('date_raw')
            ->values()
            ->toArray();

        $totals = [
            'tax_base' => round(array_sum(array_column($data, 'tax_base')), 2),
            'total_vat' => round(array_sum(array_column($data, 'total_vat')), 2),
            'total' => round(array_sum(array_column($data, 'total')), 2),
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

    // ─── Utility ───

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
}
