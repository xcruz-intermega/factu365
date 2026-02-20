<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Expense;
use App\Models\InvoicingRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // KPI: Facturado este mes (issued invoices, finalized+)
        $invoicedThisMonth = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->whereBetween('issue_date', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // KPI: Pendiente de cobro (issued invoices not paid/cancelled)
        $pendingCollection = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->whereIn('status', [
                Document::STATUS_FINALIZED,
                Document::STATUS_SENT,
                Document::STATUS_PARTIAL,
                Document::STATUS_OVERDUE,
            ])
            ->sum('total');

        // KPI: Gastos este mes
        $expensesThisMonth = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // KPI: Resultado este mes (revenue - expenses)
        $resultThisMonth = (float) $invoicedThisMonth - (float) $expensesThisMonth;

        // Overdue invoices count
        $overdueCount = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE])
            ->where('status', Document::STATUS_OVERDUE)
            ->count();

        // VeriFactu status summary
        $verifactuStats = [
            'accepted' => InvoicingRecord::where('submission_status', 'accepted')->count(),
            'pending' => InvoicingRecord::where('submission_status', 'pending')->count(),
            'rejected' => InvoicingRecord::where('submission_status', 'rejected')->count(),
            'error' => InvoicingRecord::where('submission_status', 'error')->count(),
        ];

        // Monthly evolution (last 12 months): revenue vs expenses
        $monthlyEvolution = $this->getMonthlyEvolution();

        // Recent invoices (last 5)
        $recentDocuments = Document::issued()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->with('client:id,legal_name,trade_name,nif')
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'document_type', 'number', 'client_id', 'issue_date', 'total', 'status']);

        // Recent expenses (last 5)
        $recentExpenses = Expense::with('category:id,name,code')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'concept', 'category_id', 'expense_date', 'total', 'payment_status']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'invoiced_this_month' => round((float) $invoicedThisMonth, 2),
                'pending_collection' => round((float) $pendingCollection, 2),
                'expenses_this_month' => round((float) $expensesThisMonth, 2),
                'result_this_month' => round($resultThisMonth, 2),
                'overdue_count' => $overdueCount,
            ],
            'verifactu' => $verifactuStats,
            'monthlyEvolution' => $monthlyEvolution,
            'recentDocuments' => $recentDocuments,
            'recentExpenses' => $recentExpenses,
        ]);
    }

    private function getMonthlyEvolution(): array
    {
        $months = [];
        $now = Carbon::now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $revenue = (float) Document::issued()
                ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
                ->where('status', '!=', Document::STATUS_DRAFT)
                ->whereBetween('issue_date', [$start, $end])
                ->sum('total');

            $expenses = (float) Expense::whereBetween('expense_date', [$start, $end])
                ->sum('total');

            $months[] = [
                'label' => $date->translatedFormat('M Y'),
                'month' => $date->format('Y-m'),
                'revenue' => round($revenue, 2),
                'expenses' => round($expenses, 2),
                'result' => round($revenue - $expenses, 2),
            ];
        }

        return $months;
    }
}
