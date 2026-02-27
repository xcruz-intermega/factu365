<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Document;
use App\Models\DocumentDueDate;
use App\Models\Expense;
use App\Models\TreasuryEntry;
use App\Services\ReportPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TreasuryController extends Controller
{
    public function overview(Request $request)
    {
        return Inertia::render('Treasury/Overview', $this->getOverviewData());
    }

    public function collections(Request $request)
    {
        return Inertia::render('Treasury/Collections', $this->getCollectionsData($request));
    }

    public function payments(Request $request)
    {
        return Inertia::render('Treasury/Payments', $this->getPaymentsData($request));
    }

    public function storeEntry(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'concept' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        TreasuryEntry::create([
            ...$validated,
            'entry_type' => TreasuryEntry::TYPE_MANUAL,
        ]);

        return back()->with('success', __('treasury.flash_entry_created'));
    }

    public function updateEntry(Request $request, TreasuryEntry $entry)
    {
        if ($entry->entry_type !== TreasuryEntry::TYPE_MANUAL) {
            return back()->with('error', __('treasury.error_entry_not_manual'));
        }

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'concept' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $entry->update($validated);

        return back()->with('success', __('treasury.flash_entry_updated'));
    }

    public function destroyEntry(TreasuryEntry $entry)
    {
        if ($entry->entry_type !== TreasuryEntry::TYPE_MANUAL) {
            return back()->with('error', __('treasury.error_entry_not_manual'));
        }

        $entry->delete();

        return back()->with('success', __('treasury.flash_entry_deleted'));
    }

    public function collectionsPdf(Request $request, ReportPdfService $pdfService)
    {
        $data = $this->getCollectionsData($request);

        return $pdfService->stream(
            'pdf.reports.treasury-collections',
            $data,
            'cobros_' . now()->format('Y-m-d') . '.pdf',
        );
    }

    public function collectionsCsv(Request $request)
    {
        $data = $this->getCollectionsData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                __('treasury.due_date'),
                __('treasury.document_number'),
                __('treasury.client'),
                __('treasury.nif'),
                __('treasury.amount'),
                __('treasury.days_overdue'),
            ], ';');

            foreach ($data['items'] as $item) {
                fputcsv($handle, [
                    Carbon::parse($item['due_date'])->format('d/m/Y'),
                    $item['document_number'],
                    $item['client_name'],
                    $item['client_nif'],
                    number_format($item['amount'], 2, ',', ''),
                    $item['days_overdue'] > 0 ? $item['days_overdue'] : '',
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cobros_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function paymentsPdf(Request $request, ReportPdfService $pdfService)
    {
        $data = $this->getPaymentsData($request);

        return $pdfService->stream(
            'pdf.reports.treasury-payments',
            $data,
            'pagos_' . now()->format('Y-m-d') . '.pdf',
        );
    }

    public function paymentsCsv(Request $request)
    {
        $data = $this->getPaymentsData($request);

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // Expenses section
            fputcsv($handle, ['--- ' . __('treasury.pending_expenses') . ' ---'], ';');
            fputcsv($handle, [
                __('treasury.due_date'),
                __('treasury.concept'),
                __('treasury.supplier'),
                __('treasury.amount'),
                __('treasury.days_overdue'),
            ], ';');

            foreach ($data['expenseItems'] as $item) {
                fputcsv($handle, [
                    $item['due_date'] ? Carbon::parse($item['due_date'])->format('d/m/Y') : '',
                    $item['concept'],
                    $item['supplier_name'],
                    number_format($item['amount'], 2, ',', ''),
                    $item['days_overdue'] > 0 ? $item['days_overdue'] : '',
                ], ';');
            }

            fputcsv($handle, [], ';');

            // Purchase invoices section
            fputcsv($handle, ['--- ' . __('treasury.pending_purchase_invoices') . ' ---'], ';');
            fputcsv($handle, [
                __('treasury.due_date'),
                __('treasury.document_number'),
                __('treasury.supplier'),
                __('treasury.amount'),
                __('treasury.days_overdue'),
            ], ';');

            foreach ($data['purchaseItems'] as $item) {
                fputcsv($handle, [
                    Carbon::parse($item['due_date'])->format('d/m/Y'),
                    $item['document_number'],
                    $item['supplier_name'],
                    number_format($item['amount'], 2, ',', ''),
                    $item['days_overdue'] > 0 ? $item['days_overdue'] : '',
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pagos_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    private function getOverviewData(): array
    {
        // Bank accounts with balances
        $accounts = BankAccount::where('is_active', true)->orderBy('name')->get()->map(function ($account) {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'iban' => $account->iban,
                'current_balance' => $account->currentBalance(),
            ];
        });

        $totalBalance = $accounts->sum('current_balance');

        // Cash flow: 12 months
        $cashFlow = [];
        $now = Carbon::now();
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth()->toDateString();
            $endOfMonth = $month->copy()->endOfMonth()->toDateString();

            $collections = (float) TreasuryEntry::where('entry_type', TreasuryEntry::TYPE_COLLECTION)
                ->whereBetween('entry_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $payments = (float) TreasuryEntry::where('entry_type', '!=', TreasuryEntry::TYPE_COLLECTION)
                ->whereBetween('entry_date', [$startOfMonth, $endOfMonth])
                ->sum(DB::raw('ABS(amount)'));

            $cashFlow[] = [
                'label' => $month->translatedFormat('M Y'),
                'month' => $month->format('Y-m'),
                'collections' => $collections,
                'payments' => $payments,
            ];
        }

        // KPI: collections & payments this month
        $startOfCurrentMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfCurrentMonth = $now->copy()->endOfMonth()->toDateString();

        $collectionsThisMonth = (float) TreasuryEntry::where('entry_type', TreasuryEntry::TYPE_COLLECTION)
            ->whereBetween('entry_date', [$startOfCurrentMonth, $endOfCurrentMonth])
            ->sum('amount');

        $paymentsThisMonth = (float) TreasuryEntry::whereIn('entry_type', [TreasuryEntry::TYPE_PAYMENT, TreasuryEntry::TYPE_MANUAL])
            ->whereBetween('entry_date', [$startOfCurrentMonth, $endOfCurrentMonth])
            ->where('amount', '<', 0)
            ->sum(DB::raw('ABS(amount)'));

        // Recent entries
        $recentEntries = TreasuryEntry::with('bankAccount:id,name')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'entry_date' => $entry->entry_date->toDateString(),
                    'concept' => $entry->concept,
                    'amount' => (float) $entry->amount,
                    'entry_type' => $entry->entry_type,
                    'bank_account_name' => $entry->bankAccount->name ?? '',
                    'bank_account_id' => $entry->bank_account_id,
                    'notes' => $entry->notes,
                    'is_manual' => $entry->entry_type === TreasuryEntry::TYPE_MANUAL,
                ];
            });

        return [
            'accounts' => $accounts,
            'totalBalance' => $totalBalance,
            'collectionsThisMonth' => $collectionsThisMonth,
            'paymentsThisMonth' => $paymentsThisMonth,
            'netFlow' => $collectionsThisMonth - $paymentsThisMonth,
            'cashFlow' => $cashFlow,
            'recentEntries' => $recentEntries,
        ];
    }

    private function getCollectionsData(Request $request): array
    {
        $query = DocumentDueDate::where('payment_status', 'pending')
            ->whereHas('document', function ($q) {
                $q->where('direction', 'issued')
                    ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
                    ->whereNotIn('status', [Document::STATUS_DRAFT, Document::STATUS_CANCELLED]);
            })
            ->with(['document.client:id,legal_name,trade_name,nif']);

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $dueDates = $query->orderBy('due_date')->get();

        $today = Carbon::today();
        $totalPending = 0;
        $totalOverdue = 0;

        $items = $dueDates->map(function ($dd) use ($today, &$totalPending, &$totalOverdue) {
            $amount = (float) $dd->amount;
            $totalPending += $amount;
            $dueDate = $dd->due_date;
            $isOverdue = $dueDate->lt($today);
            $daysOverdue = $isOverdue ? $dueDate->diffInDays($today) : 0;
            if ($isOverdue) {
                $totalOverdue += $amount;
            }

            return [
                'id' => $dd->id,
                'due_date' => $dueDate->toDateString(),
                'amount' => $amount,
                'is_overdue' => $isOverdue,
                'days_overdue' => $daysOverdue,
                'document_number' => $dd->document->number ?? '',
                'document_id' => $dd->document->id,
                'document_type' => $dd->document->document_type,
                'client_name' => $dd->document->client->trade_name ?: $dd->document->client->legal_name ?? '',
                'client_nif' => $dd->document->client->nif ?? '',
            ];
        });

        return [
            'items' => $items,
            'totalPending' => $totalPending,
            'totalOverdue' => $totalOverdue,
            'filters' => [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ];
    }

    private function getPaymentsData(Request $request): array
    {
        $today = Carbon::today();

        // Pending expenses
        $expenseQuery = Expense::where('payment_status', Expense::STATUS_PENDING);
        if ($request->filled('date_from')) {
            $expenseQuery->where('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $expenseQuery->where('due_date', '<=', $request->date_to);
        }
        $expenses = $expenseQuery->with('supplier:id,legal_name,trade_name')->orderBy('due_date')->get();

        $expenseTotalPending = 0;
        $expenseTotalOverdue = 0;
        $expenseItems = $expenses->map(function ($exp) use ($today, &$expenseTotalPending, &$expenseTotalOverdue) {
            $amount = (float) $exp->total;
            $expenseTotalPending += $amount;
            $dueDate = $exp->due_date;
            $isOverdue = $dueDate && $dueDate->lt($today);
            $daysOverdue = $isOverdue ? $dueDate->diffInDays($today) : 0;
            if ($isOverdue) {
                $expenseTotalOverdue += $amount;
            }

            return [
                'id' => $exp->id,
                'due_date' => $dueDate?->toDateString(),
                'concept' => $exp->concept,
                'amount' => $amount,
                'is_overdue' => $isOverdue,
                'days_overdue' => $daysOverdue,
                'supplier_name' => $exp->supplier_display_name,
            ];
        });

        // Pending purchase invoice due dates
        $purchaseQuery = DocumentDueDate::where('payment_status', 'pending')
            ->whereHas('document', function ($q) {
                $q->where('direction', 'received')
                    ->where('document_type', Document::TYPE_PURCHASE_INVOICE)
                    ->whereNotIn('status', [Document::STATUS_DRAFT, Document::STATUS_CANCELLED]);
            })
            ->with(['document.client:id,legal_name,trade_name']);

        if ($request->filled('date_from')) {
            $purchaseQuery->where('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $purchaseQuery->where('due_date', '<=', $request->date_to);
        }

        $purchaseDueDates = $purchaseQuery->orderBy('due_date')->get();

        $purchaseTotalPending = 0;
        $purchaseTotalOverdue = 0;
        $purchaseItems = $purchaseDueDates->map(function ($dd) use ($today, &$purchaseTotalPending, &$purchaseTotalOverdue) {
            $amount = (float) $dd->amount;
            $purchaseTotalPending += $amount;
            $dueDate = $dd->due_date;
            $isOverdue = $dueDate->lt($today);
            $daysOverdue = $isOverdue ? $dueDate->diffInDays($today) : 0;
            if ($isOverdue) {
                $purchaseTotalOverdue += $amount;
            }

            return [
                'id' => $dd->id,
                'due_date' => $dueDate->toDateString(),
                'amount' => $amount,
                'is_overdue' => $isOverdue,
                'days_overdue' => $daysOverdue,
                'document_number' => $dd->document->number ?? '',
                'document_id' => $dd->document->id,
                'supplier_name' => $dd->document->client->trade_name ?: $dd->document->client->legal_name ?? '',
            ];
        });

        return [
            'expenseItems' => $expenseItems,
            'expenseTotalPending' => $expenseTotalPending,
            'expenseTotalOverdue' => $expenseTotalOverdue,
            'purchaseItems' => $purchaseItems,
            'purchaseTotalPending' => $purchaseTotalPending,
            'purchaseTotalOverdue' => $purchaseTotalOverdue,
            'totalPending' => $expenseTotalPending + $purchaseTotalPending,
            'totalOverdue' => $expenseTotalOverdue + $purchaseTotalOverdue,
            'filters' => [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ];
    }
}
