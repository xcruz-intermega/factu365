<?php

return [
    // Page titles
    'overview_title' => 'Treasury',
    'collections_title' => 'Collections forecast',
    'payments_title' => 'Payments forecast',
    'bank_accounts_title' => 'Bank accounts',

    // KPI cards
    'total_balance' => 'Total balance',
    'collections_this_month' => 'Collections this month',
    'payments_this_month' => 'Payments this month',
    'net_flow' => 'Net flow',

    // Chart
    'chart_collections' => 'Collections',
    'chart_payments' => 'Payments',
    'cash_flow_12m' => 'Cash flow (12 months)',

    // Account cards
    'account_balance' => 'Current balance',

    // Recent entries table
    'recent_entries' => 'Recent entries',
    'entry_date' => 'Date',
    'concept' => 'Concept',
    'entry_type' => 'Type',
    'amount' => 'Amount',
    'account' => 'Account',
    'type_collection' => 'Collection',
    'type_payment' => 'Payment',
    'type_manual' => 'Manual',

    // Manual entry form
    'new_entry' => 'New entry',
    'edit_entry' => 'Edit entry',
    'entry_notes' => 'Notes',
    'select_account' => 'Select account',

    // Collections table
    'due_date' => 'Due date',
    'document_number' => 'Document no.',
    'client' => 'Customer',
    'nif' => 'Tax ID',
    'days_overdue' => 'Days overdue',
    'total_pending' => 'Total pending',
    'total_overdue' => 'Total overdue',

    // Payments table
    'pending_expenses' => 'Pending expenses',
    'pending_purchase_invoices' => 'Pending purchase invoices',
    'supplier' => 'Supplier',
    'total_general' => 'Grand total',

    // Bank accounts CRUD
    'new_account' => 'New account',
    'account_name' => 'Name',
    'account_iban' => 'IBAN',
    'initial_balance' => 'Initial balance',
    'opening_date' => 'Opening date',
    'is_default' => 'Default',
    'is_active' => 'Active',
    'no_accounts' => 'No bank accounts configured.',
    'configure_accounts' => 'Configure bank accounts',
    'no_entries' => 'No entries recorded.',

    // Flash messages
    'flash_account_created' => 'Bank account created.',
    'flash_account_updated' => 'Bank account updated.',
    'flash_account_deleted' => 'Bank account deleted.',
    'flash_entry_created' => 'Entry created.',
    'flash_entry_updated' => 'Entry updated.',
    'flash_entry_deleted' => 'Entry deleted.',
    'error_account_has_entries' => 'Cannot delete account because it has associated entries.',
    'error_entry_not_manual' => 'Only manual entries can be edited.',

    // Delete dialogs
    'delete_account_title' => 'Delete account',
    'delete_account_message' => 'Delete account ":name"?',
    'delete_entry_title' => 'Delete entry',
    'delete_entry_message' => 'Delete entry ":concept"?',

    // Auto-generated concepts
    'concept_collection' => 'Invoice collection :number',
    'concept_purchase_payment' => 'Purchase invoice payment :number',
    'concept_expense_payment' => 'Expense payment - :concept',

    // Filters
    'date_from' => 'From',
    'date_to' => 'To',

    // Empty states
    'no_pending_collections' => 'No pending collections.',
    'no_pending_payments' => 'No pending payments.',
];
