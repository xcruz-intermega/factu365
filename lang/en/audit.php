<?php

return [
    // Page
    'title' => 'Audit log',
    'description' => 'Complete history of operations performed in the system.',
    'no_logs' => 'No audit log entries.',

    // Filters
    'filter_action' => 'Action',
    'filter_model' => 'Entity',
    'filter_user' => 'User',
    'filter_from' => 'From',
    'filter_to' => 'To',
    'filter_search' => 'Search...',
    'filter_all_actions' => 'All actions',
    'filter_all_models' => 'All entities',
    'filter_all_users' => 'All users',
    'filter_clear' => 'Clear filters',

    // Columns
    'col_date' => 'Date',
    'col_user' => 'User',
    'col_action' => 'Action',
    'col_model' => 'Entity',
    'col_entity_id' => 'ID',
    'col_summary' => 'Summary',
    'col_ip' => 'IP',
    'col_field' => 'Field',
    'col_old_value' => 'Old value',
    'col_new_value' => 'New value',

    // Actions
    'action_created' => 'Created',
    'action_updated' => 'Updated',
    'action_deleted' => 'Deleted',
    'action_finalized' => 'Finalized',
    'action_sent_to_aeat' => 'Sent to AEAT',
    'action_marked_paid' => 'Marked paid',
    'action_status_changed' => 'Status changed',
    'action_converted' => 'Converted',
    'action_restored' => 'Restored',

    // Models
    'model_document' => 'Document',
    'model_document_line' => 'Document line',
    'model_document_series' => 'Document series',
    'model_document_due_date' => 'Due date',
    'model_expense' => 'Expense',
    'model_expense_category' => 'Expense category',
    'model_invoicing_record' => 'VeriFactu record',
    'model_aeat_submission' => 'AEAT submission',
    'model_client' => 'Client',
    'model_product' => 'Product',
    'model_product_family' => 'Product family',
    'model_product_component' => 'Product component',
    'model_client_discount' => 'Client discount',
    'model_company_profile' => 'Company profile',
    'model_certificate' => 'Certificate',
    'model_pdf_template' => 'PDF template',
    'model_payment_template' => 'Payment template',
    'model_payment_template_line' => 'Payment template line',
    'model_stock_movement' => 'Stock movement',
    'model_user' => 'User',

    // Misc
    'system' => 'System',
    'redacted' => '[REDACTED]',
    'null_value' => '(empty)',
    'expand' => 'View details',
    'collapse' => 'Hide details',
    'export_csv' => 'Export CSV',
];
