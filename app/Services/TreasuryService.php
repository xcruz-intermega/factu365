<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\DocumentDueDate;
use App\Models\Expense;
use App\Models\TreasuryEntry;

class TreasuryService
{
    public function onDueDatePaid(DocumentDueDate $dueDate): void
    {
        $account = BankAccount::where('is_default', true)->where('is_active', true)->first();
        if (! $account) {
            return;
        }

        $document = $dueDate->document;
        if (! $document) {
            return;
        }

        $isIssued = $document->direction === 'issued';
        $amount = $isIssued ? abs((float) $dueDate->amount) : -abs((float) $dueDate->amount);
        $concept = $isIssued
            ? __('treasury.concept_collection', ['number' => $document->number ?? $document->id])
            : __('treasury.concept_purchase_payment', ['number' => $document->number ?? $document->id]);

        TreasuryEntry::create([
            'bank_account_id' => $account->id,
            'entry_date' => now()->toDateString(),
            'concept' => $concept,
            'amount' => $amount,
            'entry_type' => $isIssued ? TreasuryEntry::TYPE_COLLECTION : TreasuryEntry::TYPE_PAYMENT,
            'document_due_date_id' => $dueDate->id,
        ]);
    }

    public function onDueDateUnpaid(DocumentDueDate $dueDate): void
    {
        TreasuryEntry::where('document_due_date_id', $dueDate->id)->delete();
    }

    public function onExpensePaid(Expense $expense): void
    {
        $account = BankAccount::where('is_default', true)->where('is_active', true)->first();
        if (! $account) {
            return;
        }

        TreasuryEntry::create([
            'bank_account_id' => $account->id,
            'entry_date' => now()->toDateString(),
            'concept' => __('treasury.concept_expense_payment', ['concept' => $expense->concept]),
            'amount' => -abs((float) $expense->total),
            'entry_type' => TreasuryEntry::TYPE_PAYMENT,
            'expense_id' => $expense->id,
        ]);
    }

    public function onExpenseUnpaid(Expense $expense): void
    {
        TreasuryEntry::where('expense_id', $expense->id)->delete();
    }
}
