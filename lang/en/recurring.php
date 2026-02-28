<?php

return [
    // Page titles
    'title_index' => 'Recurring Invoices',
    'title_create' => 'New Recurring Invoice',
    'title_edit' => 'Edit Recurring Invoice',
    'title_show' => 'Recurring Invoice Details',

    // Form sections
    'section_info' => 'Information',
    'section_recurrence' => 'Recurrence',
    'section_behavior' => 'Behaviour',
    'section_lines' => 'Lines',
    'section_additional' => 'Additional',

    // Form labels
    'label_name' => 'Name',
    'label_name_placeholder' => 'E.g.: Monthly website maintenance',
    'label_client' => 'Client',
    'label_series' => 'Series',
    'label_payment_template' => 'Payment template',
    'label_invoice_type' => 'Invoice type',
    'label_regime_key' => 'Tax regime key',
    'label_global_discount' => 'Global discount (%)',
    'label_notes' => 'Notes',
    'label_footer_text' => 'Footer text',
    'label_start_date' => 'Start date',
    'label_end_date' => 'End date',
    'label_interval_value' => 'Every',
    'label_interval_unit' => 'Unit',
    'label_max_occurrences' => 'Max. occurrences',
    'label_auto_finalize' => 'Auto-finalise',
    'label_auto_finalize_help' => 'Invoices will be automatically finalised with a fiscal number. They will be registered in VeriFactu.',
    'label_auto_send_email' => 'Auto-send by email',
    'label_auto_send_email_help' => 'Only works if auto-finalise is enabled and SMTP is configured.',
    'label_email_recipients' => 'Email recipients',
    'label_email_recipients_help' => 'Comma-separated. If empty, the client email is used.',
    'label_next_issue_date' => 'Next issue date',
    'label_occurrences_count' => 'Generated',
    'label_created_by' => 'Created by',

    // Interval units
    'unit_day' => 'day(s)',
    'unit_week' => 'week(s)',
    'unit_month' => 'month(s)',
    'unit_year' => 'year(s)',

    // Frequency display
    'frequency_every' => 'Every :value :unit',

    // Statuses
    'status_active' => 'Active',
    'status_paused' => 'Paused',
    'status_finished' => 'Finished',

    // Table columns
    'col_name' => 'Name',
    'col_client' => 'Client',
    'col_frequency' => 'Frequency',
    'col_next_date' => 'Next issue',
    'col_status' => 'Status',
    'col_generated' => 'Generated',
    'col_actions' => 'Actions',

    // Actions
    'btn_create' => 'New recurring',
    'btn_save' => 'Save',
    'btn_update' => 'Update',
    'btn_edit' => 'Edit',
    'btn_delete' => 'Delete',
    'btn_pause' => 'Pause',
    'btn_resume' => 'Resume',
    'btn_generate_now' => 'Generate now',
    'btn_back' => 'Back',

    // Show page
    'show_config' => 'Configuration',
    'show_history' => 'Generated invoices history',
    'show_no_documents' => 'No invoices have been generated yet.',
    'show_document_number' => 'Number',
    'show_document_date' => 'Date',
    'show_document_total' => 'Total',
    'show_document_status' => 'Status',
    'show_unlimited' => 'Unlimited',

    // Flash messages
    'flash_created' => 'Recurring invoice created successfully.',
    'flash_updated' => 'Recurring invoice updated successfully.',
    'flash_deleted' => 'Recurring invoice deleted.',
    'flash_paused' => 'Recurring invoice paused.',
    'flash_resumed' => 'Recurring invoice resumed.',
    'flash_generated' => 'Invoice generated: :number',

    // Errors
    'error_cannot_delete_active' => 'Cannot delete an active recurring invoice that has generated invoices. Pause it first.',
    'error_finished' => 'This recurring invoice has already finished.',
    'error_not_active' => 'Can only generate invoices from active recurring templates.',
    'error_generation_failed' => 'Error generating invoice: :error',

    // Confirm dialogs
    'confirm_delete_title' => 'Delete recurring invoice',
    'confirm_delete_message' => 'Are you sure you want to delete the recurring invoice ":name"?',
    'confirm_generate_title' => 'Generate invoice now',
    'confirm_generate_message' => 'An invoice will be generated immediately from template ":name".',

    // Empty state
    'empty_title' => 'No recurring invoices',
    'empty_description' => 'Create your first recurring invoice to automate periodic billing.',

    // Validation
    'validation_name_required' => 'Name is required.',
    'validation_client_required' => 'Client is required.',
    'validation_interval_required' => 'Interval is required.',
    'validation_start_date_required' => 'Start date is required.',
    'validation_lines_required' => 'At least one line is required.',
    'validation_concept_required' => 'Concept is required for each line.',
    'validation_quantity_required' => 'Quantity is required.',
    'validation_unit_price_required' => 'Unit price is required.',
    'validation_vat_rate_required' => 'VAT rate is required.',

    // Badge on document edit
    'badge_from_recurring' => 'Generated from: :name',
];
