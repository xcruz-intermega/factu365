<?php

return [
    // Page titles
    'overview_title' => 'Tresoreria',
    'collections_title' => 'Previsió de cobraments',
    'payments_title' => 'Previsió de pagaments',
    'bank_accounts_title' => 'Comptes bancaris',

    // KPI cards
    'total_balance' => 'Saldo total',
    'collections_this_month' => 'Cobraments aquest mes',
    'payments_this_month' => 'Pagaments aquest mes',
    'net_flow' => 'Flux net',

    // Chart
    'chart_collections' => 'Cobraments',
    'chart_payments' => 'Pagaments',
    'cash_flow_12m' => 'Flux de caixa (12 mesos)',

    // Account cards
    'account_balance' => 'Saldo actual',

    // Recent entries table
    'recent_entries' => 'Últims moviments',
    'entry_date' => 'Data',
    'concept' => 'Concepte',
    'entry_type' => 'Tipus',
    'amount' => 'Import',
    'account' => 'Compte',
    'type_collection' => 'Cobrament',
    'type_payment' => 'Pagament',
    'type_manual' => 'Manual',

    // Manual entry form
    'new_entry' => 'Nou apunt',
    'edit_entry' => 'Editar apunt',
    'entry_notes' => 'Notes',
    'select_account' => 'Seleccionar compte',

    // Collections table
    'due_date' => 'Venciment',
    'document_number' => 'Núm. document',
    'client' => 'Client',
    'nif' => 'NIF',
    'days_overdue' => 'Dies vençuts',
    'total_pending' => 'Total pendent',
    'total_overdue' => 'Total vençut',

    // Payments table
    'pending_expenses' => 'Despeses pendents',
    'pending_purchase_invoices' => 'Factures compra pendents',
    'supplier' => 'Proveïdor',
    'total_general' => 'Total general',

    // Bank accounts CRUD
    'new_account' => 'Nou compte',
    'account_name' => 'Nom',
    'account_iban' => 'IBAN',
    'initial_balance' => 'Saldo inicial',
    'opening_date' => 'Data obertura',
    'is_default' => 'Per defecte',
    'is_active' => 'Actiu',
    'no_accounts' => 'No hi ha comptes bancaris configurats.',
    'configure_accounts' => 'Configurar comptes bancaris',
    'no_entries' => 'No hi ha moviments registrats.',

    // Flash messages
    'flash_account_created' => 'Compte bancari creat.',
    'flash_account_updated' => 'Compte bancari actualitzat.',
    'flash_account_deleted' => 'Compte bancari eliminat.',
    'flash_entry_created' => 'Apunt creat.',
    'flash_entry_updated' => 'Apunt actualitzat.',
    'flash_entry_deleted' => 'Apunt eliminat.',
    'error_account_has_entries' => 'No es pot eliminar el compte perquè té moviments associats.',
    'error_entry_not_manual' => 'Només es poden editar apunts manuals.',

    // Delete dialogs
    'delete_account_title' => 'Eliminar compte',
    'delete_account_message' => 'Eliminar el compte ":name"?',
    'delete_entry_title' => 'Eliminar apunt',
    'delete_entry_message' => 'Eliminar l\'apunt ":concept"?',

    // Auto-generated concepts
    'concept_collection' => 'Cobrament factura :number',
    'concept_purchase_payment' => 'Pagament factura compra :number',
    'concept_expense_payment' => 'Pagament despesa - :concept',

    // Filters
    'date_from' => 'Des de',
    'date_to' => 'Fins a',

    // Empty states
    'no_pending_collections' => 'No hi ha cobraments pendents.',
    'no_pending_payments' => 'No hi ha pagaments pendents.',
];
