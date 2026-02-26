<?php

return [
    // Document type labels
    'type_invoice' => 'Factura',
    'type_quote' => 'Pressupost',
    'type_delivery_note' => 'Albarà',
    'type_rectificative' => 'Rectificativa',
    'type_purchase_invoice' => 'Factura rebuda',

    // Lowercase for conversion labels
    'to_invoice' => 'factura',
    'to_delivery_note' => 'albarà',
    'to_quote' => 'pressupost',

    // Index
    'col_number' => 'Número',
    'col_client' => 'Client',
    'col_date' => 'Data',
    'col_total' => 'Total',
    'col_status' => 'Estat',
    'search_placeholder' => 'Cercar per número o client...',
    'all_statuses' => 'Tots els estats',
    'delete_title' => 'Eliminar document',

    // Per-type titles (gender-aware)
    'title_invoice' => 'Factures',
    'title_quote' => 'Pressupostos',
    'title_delivery_note' => 'Albarans',
    'title_rectificative' => 'Rectificatives',
    'title_purchase_invoice' => 'Factures rebudes',
    'new_invoice' => 'Nova factura',
    'new_quote' => 'Nou pressupost',
    'new_delivery_note' => 'Nou albarà',
    'new_rectificative' => 'Nova rectificativa',
    'new_purchase_invoice' => 'Nova factura rebuda',
    'delete_message' => 'Esteu segur que voleu eliminar aquest document? Aquesta acció no es pot desfer.',

    // Create / Edit
    'edit_header' => ':type :number',

    // Actions bar
    'actions_label' => 'Accions:',
    'mark_sent' => 'Marcar enviat',
    'mark_sent_f' => 'Marcar enviada',
    'accept' => 'Acceptar',
    'reject' => 'Rebutjar',
    'mark_paid' => 'Marcar pagada',
    'partial_payment' => 'Pagament parcial',
    'mark_overdue' => 'Marcar vençuda',
    'cancel_doc' => 'Anul·lar',
    'convert_to' => 'Convertir a :target',
    'create_rectificative' => 'Crear rectificativa',
    'finalize_document' => 'Finalitzar :type',

    // Finalize dialog
    'finalize_title' => 'Finalitzar document',
    'finalize_message' => 'En finalitzar s\'assignarà un número de sèrie i el document no podrà ser editat. Continuar?',

    // Email dialog
    'email_title' => 'Enviar document per email',
    'email_to' => 'Destinatari *',
    'email_to_placeholder' => 'email@exemple.com',
    'email_subject' => 'Assumpte',
    'email_message' => 'Missatge',

    // Read-only summary
    'issue_date' => 'Data emissió',

    // Lines table (read-only in Edit)
    'col_concept' => 'Concepte',
    'col_quantity' => 'Qttat.',
    'col_price' => 'Preu',

    // Due dates section
    'due_dates' => 'Venciments',
    'col_percent' => '%',
    'col_amount' => 'Import',
    'col_action' => 'Acció',
    'paid' => 'Pagat',
    'unmark' => 'Desmarcar',
    'mark_due_paid' => 'Marcar pagat',

    // Form
    'general_data' => 'Dades generals',
    'client' => 'Client',
    'quote_title' => 'Títol del pressupost',
    'quote_title_placeholder' => 'Ex: Pressupost per a Jardineria 400h',
    'invoice_type' => 'Tipus factura',
    'series' => 'Sèrie',
    'issue_date_label' => 'Data emissió *',
    'due_date' => 'Data venciment',
    'operation_date' => 'Data operació',
    'rectification_method' => 'Mètode rectificació',
    'rectification_substitution' => 'Per substitució',
    'rectification_differences' => 'Per diferències',
    'search_client' => 'Cercar client...',

    // Due dates form
    'select_template' => 'Seleccionar plantilla...',
    'add_due_date' => '+ Afegir',
    'no_due_dates' => 'Sense venciments configurats. S\'utilitzarà la data de venciment simple.',

    // Notes
    'notes_footer' => 'Notes i peu',
    'internal_notes' => 'Notes internes',
    'document_footer' => 'Peu de document',
    'internal_notes_placeholder' => 'Notes internes (no es mostren al client)',
    'footer_placeholder' => 'Text visible al peu del document',

    // Submit
    'create_document' => 'Crear :type',
    'create_draft' => 'Crear esborrany',

    // Document type labels (full)
    'type_proforma' => 'Proforma',
    'type_receipt' => 'Rebut',

    // Lines editor
    'document_lines' => 'Línies del document',
    'add_line' => 'Afegir línia',
    'add_another_line' => 'Afegir una altra línia',
    'no_lines' => 'No hi ha línies. Feu clic a "Afegir línia" per començar.',
    'product' => 'Producte',
    'concept_required' => 'Concepte *',
    'concept_placeholder' => 'Concepte de la línia',
    'quantity_required' => 'Quantitat *',
    'unit_price_required' => 'Preu unitari *',
    'unit' => 'Unitat',
    'unit_short' => 'ut.',
    'discount_pct' => 'Dte. %',
    'client_discount' => 'Dte. client',
    'vat_pct' => 'IVA %',
    'exemption_required' => 'Exempció *',
    'irpf_pct' => 'IRPF %',
    'surcharge_label' => 'R.E.',
    'search_product' => 'Cercar producte...',
    'remove_line' => 'Eliminar línia',
    'components_count' => ':count comp.',
    'expand_components' => 'Expandir components (:count)',
    'expand_components_help' => 'Substitueix aquesta línia pels seus components individuals',

    // Totals summary
    'summary' => 'Resum',
    'line_discounts' => 'Descomptes línia',
    'global_discount' => 'Dte. global',

    // Flash messages (per-type, gender-aware)
    'flash_created_invoice' => 'Factura creada correctament.',
    'flash_created_quote' => 'Pressupost creat correctament.',
    'flash_created_delivery_note' => 'Albarà creat correctament.',
    'flash_created_rectificative' => 'Rectificativa creada correctament.',
    'flash_created_purchase_invoice' => 'Factura rebuda creada correctament.',
    'flash_updated_invoice' => 'Factura actualitzada correctament.',
    'flash_updated_quote' => 'Pressupost actualitzat correctament.',
    'flash_updated_delivery_note' => 'Albarà actualitzat correctament.',
    'flash_updated_rectificative' => 'Rectificativa actualitzada correctament.',
    'flash_updated_purchase_invoice' => 'Factura rebuda actualitzada correctament.',
    'flash_deleted_invoice' => 'Factura eliminada correctament.',
    'flash_deleted_quote' => 'Pressupost eliminat correctament.',
    'flash_deleted_delivery_note' => 'Albarà eliminat correctament.',
    'flash_deleted_rectificative' => 'Rectificativa eliminada correctament.',
    'flash_deleted_purchase_invoice' => 'Factura rebuda eliminada correctament.',
    'flash_finalized_invoice' => 'Factura finalitzada amb número :number.',
    'flash_finalized_rectificative' => 'Rectificativa finalitzada amb número :number.',
    'flash_finalized_purchase_invoice' => 'Factura rebuda finalitzada amb número :number.',
    'flash_converted' => 'Document convertit a :type.',
    'flash_rectificative_created' => 'Factura rectificativa creada com a esborrany.',
    'flash_status_updated' => 'Estat actualitzat a :status.',
    'flash_email_sent' => 'Document enviat per email a :email.',
    'flash_due_date_paid' => 'Venciment marcat com a pagat.',
    'flash_due_date_unpaid' => 'Venciment desmarcat.',
    'flash_email_body' => 'Adjunt trobareu el document :type :number.',
    'error_cannot_edit' => 'Aquest document no pot ser editat.',
    'error_no_finalize_needed' => 'Els pressupostos i albarans no requereixen finalització.',
    'error_cannot_finalize' => 'Aquest document no pot ser finalitzat. Verifiqueu que té client i línies.',
    'error_cannot_delete' => 'Aquest document no pot ser eliminat.',
    'error_cannot_convert' => 'Aquest document no pot ser convertit.',
    'error_invalid_conversion' => 'Tipus de conversió no vàlid.',
    'error_only_finalized' => 'Només es poden rectificar factures finalitzades.',
    'error_draft_status' => 'No es pot canviar l\'estat d\'un esborrany.',
    'error_invalid_status' => 'Estat no vàlid.',
    'error_draft_email' => 'No es pot enviar un document en esborrany.',

    // Accounted
    'col_accounted' => 'Comptab.',
    'all_accounted' => 'Comptabilització',
    'filter_accounted' => 'Comptabilitzada',
    'filter_not_accounted' => 'No comptabilitzada',
    'flash_accounted' => 'Document marcat com a comptabilitzat.',
    'flash_unaccounted' => 'Document desmarcat com a comptabilitzat.',
    'error_not_accountable' => 'Aquest tipus de document no admet comptabilització.',
];
