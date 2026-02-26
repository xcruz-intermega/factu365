<?php

return [
    // Page titles
    'overview_title' => 'Tesorería',
    'collections_title' => 'Previsión de cobros',
    'payments_title' => 'Previsión de pagos',
    'bank_accounts_title' => 'Cuentas bancarias',

    // KPI cards
    'total_balance' => 'Saldo total',
    'collections_this_month' => 'Cobros este mes',
    'payments_this_month' => 'Pagos este mes',
    'net_flow' => 'Flujo neto',

    // Chart
    'chart_collections' => 'Cobros',
    'chart_payments' => 'Pagos',
    'cash_flow_12m' => 'Flujo de caja (12 meses)',

    // Account cards
    'account_balance' => 'Saldo actual',

    // Recent entries table
    'recent_entries' => 'Últimos movimientos',
    'entry_date' => 'Fecha',
    'concept' => 'Concepto',
    'entry_type' => 'Tipo',
    'amount' => 'Importe',
    'account' => 'Cuenta',
    'type_collection' => 'Cobro',
    'type_payment' => 'Pago',
    'type_manual' => 'Manual',

    // Manual entry form
    'new_entry' => 'Nuevo apunte',
    'edit_entry' => 'Editar apunte',
    'entry_notes' => 'Notas',
    'select_account' => 'Seleccionar cuenta',

    // Collections table
    'due_date' => 'Vencimiento',
    'document_number' => 'Nº documento',
    'client' => 'Cliente',
    'nif' => 'NIF',
    'days_overdue' => 'Días vencidos',
    'total_pending' => 'Total pendiente',
    'total_overdue' => 'Total vencido',

    // Payments table
    'pending_expenses' => 'Gastos pendientes',
    'pending_purchase_invoices' => 'Facturas compra pendientes',
    'supplier' => 'Proveedor',
    'total_general' => 'Total general',

    // Bank accounts CRUD
    'new_account' => 'Nueva cuenta',
    'account_name' => 'Nombre',
    'account_iban' => 'IBAN',
    'initial_balance' => 'Saldo inicial',
    'opening_date' => 'Fecha apertura',
    'is_default' => 'Por defecto',
    'is_active' => 'Activa',
    'no_accounts' => 'No hay cuentas bancarias configuradas.',
    'configure_accounts' => 'Configurar cuentas bancarias',
    'no_entries' => 'No hay movimientos registrados.',

    // Flash messages
    'flash_account_created' => 'Cuenta bancaria creada.',
    'flash_account_updated' => 'Cuenta bancaria actualizada.',
    'flash_account_deleted' => 'Cuenta bancaria eliminada.',
    'flash_entry_created' => 'Apunte creado.',
    'flash_entry_updated' => 'Apunte actualizado.',
    'flash_entry_deleted' => 'Apunte eliminado.',
    'error_account_has_entries' => 'No se puede eliminar la cuenta porque tiene movimientos asociados.',
    'error_entry_not_manual' => 'Solo se pueden editar apuntes manuales.',

    // Delete dialogs
    'delete_account_title' => 'Eliminar cuenta',
    'delete_account_message' => '¿Eliminar la cuenta ":name"?',
    'delete_entry_title' => 'Eliminar apunte',
    'delete_entry_message' => '¿Eliminar el apunte ":concept"?',

    // Auto-generated concepts
    'concept_collection' => 'Cobro factura :number',
    'concept_purchase_payment' => 'Pago factura compra :number',
    'concept_expense_payment' => 'Pago gasto - :concept',

    // Filters
    'date_from' => 'Desde',
    'date_to' => 'Hasta',

    // Empty states
    'no_pending_collections' => 'No hay cobros pendientes.',
    'no_pending_payments' => 'No hay pagos pendientes.',
];
