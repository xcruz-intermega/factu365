<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::query()
            ->with(['category:id,name,code', 'supplier:id,legal_name,trade_name'])
            ->search($request->input('search'))
            ->when($request->input('category'), fn ($q, $cat) => $q->where('category_id', $cat))
            ->when($request->input('status'), fn ($q, $status) => $q->where('payment_status', $status))
            ->when($request->input('date_from'), fn ($q, $from) => $q->where('expense_date', '>=', $from))
            ->when($request->input('date_to'), fn ($q, $to) => $q->where('expense_date', '<=', $to))
            ->when(
                $request->input('sort'),
                fn ($q) => $q->orderBy($request->input('sort'), $request->input('dir', 'desc')),
                fn ($q) => $q->orderBy('expense_date', 'desc')->orderBy('id', 'desc')
            )
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::orderBy('sort_order')->get(['id', 'name', 'code']),
            'filters' => $request->only(['search', 'category', 'status', 'date_from', 'date_to', 'sort', 'dir']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Expenses/Create', [
            'categories' => ExpenseCategory::orderBy('sort_order')->get(['id', 'name', 'code']),
            'suppliers' => Client::suppliers()->orderBy('legal_name')->get(['id', 'legal_name', 'trade_name', 'nif']),
        ]);
    }

    public function store(ExpenseRequest $request)
    {
        $validated = $request->validated();

        // Calculate amounts
        $subtotal = (float) $validated['subtotal'];
        $vatRate = (float) $validated['vat_rate'];
        $irpfRate = (float) ($validated['irpf_rate'] ?? 0);
        $vatAmount = round($subtotal * $vatRate / 100, 2);
        $irpfAmount = round($subtotal * $irpfRate / 100, 2);
        $total = round($subtotal + $vatAmount - $irpfAmount, 2);

        // Handle attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('expenses', 'private');
        }

        Expense::create([
            ...$validated,
            'vat_amount' => $vatAmount,
            'irpf_amount' => $irpfAmount,
            'total' => $total,
            'attachment_path' => $attachmentPath,
            'payment_status' => $validated['payment_status'] ?? 'pending',
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto registrado correctamente.');
    }

    public function edit(Expense $expense)
    {
        $expense->load(['category', 'supplier']);

        return Inertia::render('Expenses/Edit', [
            'expense' => $expense,
            'categories' => ExpenseCategory::orderBy('sort_order')->get(['id', 'name', 'code']),
            'suppliers' => Client::suppliers()->orderBy('legal_name')->get(['id', 'legal_name', 'trade_name', 'nif']),
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $validated = $request->validated();

        // Calculate amounts
        $subtotal = (float) $validated['subtotal'];
        $vatRate = (float) $validated['vat_rate'];
        $irpfRate = (float) ($validated['irpf_rate'] ?? 0);
        $vatAmount = round($subtotal * $vatRate / 100, 2);
        $irpfAmount = round($subtotal * $irpfRate / 100, 2);
        $total = round($subtotal + $vatAmount - $irpfAmount, 2);

        // Handle attachment
        $attachmentPath = $expense->attachment_path;
        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($attachmentPath) {
                Storage::disk('private')->delete($attachmentPath);
            }
            $attachmentPath = $request->file('attachment')->store('expenses', 'private');
        }

        $expense->update([
            ...$validated,
            'vat_amount' => $vatAmount,
            'irpf_amount' => $irpfAmount,
            'total' => $total,
            'attachment_path' => $attachmentPath,
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->attachment_path) {
            Storage::disk('private')->delete($expense->attachment_path);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto eliminado correctamente.');
    }

    public function markPaid(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        $expense->update([
            'payment_status' => Expense::STATUS_PAID,
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'] ?? null,
        ]);

        return back()->with('success', 'Gasto marcado como pagado.');
    }
}
