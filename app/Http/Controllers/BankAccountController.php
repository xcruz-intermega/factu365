<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::orderBy('name')->get()->map(function ($account) {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'iban' => $account->iban,
                'initial_balance' => $account->initial_balance,
                'opening_date' => $account->opening_date->toDateString(),
                'is_default' => $account->is_default,
                'is_active' => $account->is_active,
                'current_balance' => $account->currentBalance(),
                'entries_count' => $account->entries()->count(),
            ];
        });

        return Inertia::render('Settings/BankAccounts', [
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:34'],
            'initial_balance' => ['required', 'numeric'],
            'opening_date' => ['required', 'date'],
            'is_default' => ['boolean'],
        ]);

        if ($validated['is_default'] ?? false) {
            BankAccount::where('is_default', true)->update(['is_default' => false]);
        }

        BankAccount::create($validated);

        return back()->with('success', __('treasury.flash_account_created'));
    }

    public function update(Request $request, BankAccount $account)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:34'],
            'initial_balance' => ['required', 'numeric'],
            'opening_date' => ['required', 'date'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if ($validated['is_default'] ?? false) {
            BankAccount::where('is_default', true)->where('id', '!=', $account->id)->update(['is_default' => false]);
        }

        $account->update($validated);

        return back()->with('success', __('treasury.flash_account_updated'));
    }

    public function destroy(BankAccount $account)
    {
        if ($account->entries()->exists()) {
            return back()->with('error', __('treasury.error_account_has_entries'));
        }

        $account->delete();

        return back()->with('success', __('treasury.flash_account_deleted'));
    }
}
