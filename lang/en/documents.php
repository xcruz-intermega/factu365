<?php

return [
    // Document type labels
    'type_invoice' => 'Invoice',
    'type_quote' => 'Quote',
    'type_delivery_note' => 'Delivery note',
    'type_rectificative' => 'Credit note',
    'type_purchase_invoice' => 'Purchase invoice',

    // Lowercase for conversion labels
    'to_invoice' => 'invoice',
    'to_delivery_note' => 'delivery note',
    'to_quote' => 'quote',

    // Index
    'col_number' => 'Number',
    'col_client' => 'Client',
    'col_date' => 'Date',
    'col_total' => 'Total',
    'col_status' => 'Status',
    'search_placeholder' => 'Search by number or client...',
    'all_statuses' => 'All statuses',
    'delete_title' => 'Delete document',

    // Per-type titles
    'title_invoice' => 'Invoices',
    'title_quote' => 'Quotes',
    'title_delivery_note' => 'Delivery notes',
    'title_rectificative' => 'Credit notes',
    'title_purchase_invoice' => 'Purchase invoices',
    'new_invoice' => 'New invoice',
    'new_quote' => 'New quote',
    'new_delivery_note' => 'New delivery note',
    'new_rectificative' => 'New credit note',
    'new_purchase_invoice' => 'New purchase invoice',
    'delete_message' => 'Are you sure you want to delete this document? This action cannot be undone.',

    // Create / Edit
    'edit_header' => ':type :number',

    // Actions bar
    'actions_label' => 'Actions:',
    'mark_sent' => 'Mark as sent',
    'mark_sent_f' => 'Mark as sent',
    'accept' => 'Accept',
    'reject' => 'Reject',
    'mark_paid' => 'Mark as paid',
    'partial_payment' => 'Partial payment',
    'mark_overdue' => 'Mark as overdue',
    'cancel_doc' => 'Cancel',
    'convert_to' => 'Convert to :target',
    'create_rectificative' => 'Create credit note',
    'finalize_document' => 'Finalise :type',

    // Finalize dialog
    'finalize_title' => 'Finalise document',
    'finalize_message' => 'Finalising will assign a series number and the document will no longer be editable. Continue?',

    // Email dialog
    'email_title' => 'Send document by email',
    'email_to' => 'Recipient *',
    'email_to_placeholder' => 'email@example.com',
    'email_subject' => 'Subject',
    'email_message' => 'Message',
    'email_attachments' => 'Attachments',
    'email_attach_pdf' => 'PDF',
    'email_attach_facturae' => 'FacturaE (XML)',
    'email_attach_both' => 'Both (PDF + FacturaE)',

    // Read-only summary
    'issue_date' => 'Issue date',

    // Lines table (read-only in Edit)
    'col_concept' => 'Description',
    'col_quantity' => 'Qty',
    'col_price' => 'Price',

    // Due dates section
    'due_dates' => 'Due dates',
    'col_percent' => '%',
    'col_amount' => 'Amount',
    'col_action' => 'Action',
    'paid' => 'Paid',
    'unmark' => 'Unmark',
    'mark_due_paid' => 'Mark as paid',

    // Form
    'general_data' => 'General data',
    'client' => 'Client',
    'quote_title' => 'Quote title',
    'quote_title_placeholder' => 'E.g.: Quote for Gardening 400h',
    'invoice_type' => 'Invoice type',
    'series' => 'Series',
    'issue_date_label' => 'Issue date *',
    'due_date' => 'Due date',
    'operation_date' => 'Operation date',
    'rectification_method' => 'Rectification method',
    'rectification_substitution' => 'By substitution',
    'rectification_differences' => 'By differences',
    'search_client' => 'Search client...',

    // Due dates form
    'select_template' => 'Select template...',
    'add_due_date' => '+ Add',
    'no_due_dates' => 'No due dates configured. The simple due date will be used.',

    // Notes
    'notes_footer' => 'Notes and footer',
    'internal_notes' => 'Internal notes',
    'document_footer' => 'Document footer',
    'internal_notes_placeholder' => 'Internal notes (not shown to the client)',
    'footer_placeholder' => 'Text visible in the document footer',

    // Submit
    'create_document' => 'Create :type',
    'create_draft' => 'Create draft',

    // Lines editor
    'document_lines' => 'Document lines',
    'add_line' => 'Add line',
    'add_another_line' => 'Add another line',
    'no_lines' => 'No lines. Click "Add line" to start.',
    'product' => 'Product',
    'concept_required' => 'Description *',
    'concept_placeholder' => 'Line description',
    'quantity_required' => 'Quantity *',
    'unit_price_required' => 'Unit price *',
    'unit' => 'Unit',
    'unit_short' => 'unit',
    'discount_pct' => 'Disc. %',
    'client_discount' => 'Client disc.',
    'vat_pct' => 'VAT %',
    'exemption_required' => 'Exemption *',
    'irpf_pct' => 'IRPF %',
    'surcharge_label' => 'E.S.',
    'search_product' => 'Search product...',
    'remove_line' => 'Remove line',
    'components_count' => ':count comp.',
    'expand_components' => 'Expand components (:count)',
    'expand_components_help' => 'Replaces this line with its individual components',

    // Totals summary
    'summary' => 'Summary',
    'line_discounts' => 'Line discounts',
    'global_discount' => 'Global discount',

    // Document type labels (full)
    'type_proforma' => 'Proforma',
    'type_receipt' => 'Receipt',

    // Flash messages (per-type)
    'flash_created_invoice' => 'Invoice created successfully.',
    'flash_created_quote' => 'Quote created successfully.',
    'flash_created_delivery_note' => 'Delivery note created successfully.',
    'flash_created_rectificative' => 'Credit note created successfully.',
    'flash_created_purchase_invoice' => 'Purchase invoice created successfully.',
    'flash_updated_invoice' => 'Invoice updated successfully.',
    'flash_updated_quote' => 'Quote updated successfully.',
    'flash_updated_delivery_note' => 'Delivery note updated successfully.',
    'flash_updated_rectificative' => 'Credit note updated successfully.',
    'flash_updated_purchase_invoice' => 'Purchase invoice updated successfully.',
    'flash_deleted_invoice' => 'Invoice deleted successfully.',
    'flash_deleted_quote' => 'Quote deleted successfully.',
    'flash_deleted_delivery_note' => 'Delivery note deleted successfully.',
    'flash_deleted_rectificative' => 'Credit note deleted successfully.',
    'flash_deleted_purchase_invoice' => 'Purchase invoice deleted successfully.',
    'flash_finalized_invoice' => 'Invoice finalised with number :number.',
    'flash_finalized_rectificative' => 'Credit note finalised with number :number.',
    'flash_finalized_purchase_invoice' => 'Purchase invoice finalised with number :number.',
    'flash_converted' => 'Document converted to :type.',
    'flash_rectificative_created' => 'Credit note created as draft.',
    'flash_status_updated' => 'Status updated to :status.',
    'flash_email_sent' => 'Document sent by email to :email.',
    'flash_due_date_paid' => 'Due date marked as paid.',
    'flash_due_date_unpaid' => 'Due date unmarked.',
    'flash_email_body' => 'Please find attached the document :type :number.',
    'error_cannot_edit' => 'This document cannot be edited.',
    'error_no_finalize_needed' => 'Quotes and delivery notes do not require finalisation.',
    'error_cannot_finalize' => 'This document cannot be finalised. Please verify it has a client and lines.',
    'error_cannot_delete' => 'This document cannot be deleted.',
    'error_cannot_convert' => 'This document cannot be converted.',
    'error_invalid_conversion' => 'Invalid conversion type.',
    'error_only_finalized' => 'Only finalised invoices can be rectified.',
    'error_draft_status' => 'Cannot change the status of a draft.',
    'error_invalid_status' => 'Invalid status.',
    'error_draft_email' => 'Cannot send a draft document.',

    // E-Invoice Import
    'import_title' => 'Import e-invoice',
    'import_drop_zone' => 'Drop your e-invoice here',
    'import_formats' => 'Supported formats: FacturaE (XML), Factur-X (PDF)',
    'import_select_file' => 'Select file',
    'import_validation_errors' => 'Validation errors found',
    'import_error_no_number' => 'Invoice number not found in the file.',
    'import_error_no_nif' => 'Supplier tax ID not found in the file.',
    'import_error_totals_mismatch' => 'Calculated totals do not match the file totals.',
    'import_error_own_nif' => 'The supplier tax ID matches your company. You cannot import your own invoices.',
    'import_error_no_xml_in_pdf' => 'No embedded XML found in the PDF. The file is not a valid Factur-X.',
    'import_error_unsupported_format' => 'Unsupported file format. Use XML (FacturaE) or PDF (Factur-X).',
    'import_supplier_info' => 'Supplier information',
    'import_supplier_name' => 'Name',
    'import_link_supplier' => 'Link to existing supplier',
    'import_invoice_info' => 'Invoice information',
    'import_invoice_number' => 'Invoice number',
    'import_issue_date' => 'Issue date',
    'import_lines' => 'Lines',
    'import_upload_another' => 'Upload another file',
    'import_button' => 'Import invoice',
    'import_success' => 'Invoice imported successfully.',

    // Accounted
    'col_accounted' => 'Acctd.',
    'all_accounted' => 'Accounting',
    'filter_accounted' => 'Accounted',
    'filter_not_accounted' => 'Not accounted',
    'flash_accounted' => 'Document marked as accounted.',
    'flash_unaccounted' => 'Document unmarked as accounted.',
    'error_not_accountable' => 'This document type does not support accounting status.',

    // FacturaE export
    'download_facturae' => 'Download FacturaE',
    'error_facturae_type' => 'Only invoices and credit notes can be exported as FacturaE.',
    'error_facturae_draft' => 'Finalise the document before exporting as FacturaE.',

    // FacturaE bulk export
    'export_facturae_title' => 'Export FacturaE',
    'export_facturae_subtitle' => 'Bulk export in FacturaE 3.2.2 XML format',
    'export_date_from' => 'Date from',
    'export_date_to' => 'Date to',
    'export_all_statuses' => 'All statuses',
    'export_all_clients' => 'All clients',
    'export_filter' => 'Filter',
    'export_count' => ':count documents found',
    'export_download_zip' => 'Download FacturaE ZIP (:count docs)',
    'export_no_results' => 'No documents match the filters',
    'export_error_empty' => 'No documents to export',
    'export_error_limit' => 'Maximum 500 documents per export',
    'error_email_failed' => 'Could not send the email. Please try again later.',
];
