<?php

return [
    // Page titles
    'title_index' => 'Factures Recurrents',
    'title_create' => 'Nova Recurrent',
    'title_edit' => 'Editar Recurrent',
    'title_show' => 'Detall Recurrent',

    // Form sections
    'section_info' => 'Informació',
    'section_recurrence' => 'Recurrència',
    'section_behavior' => 'Comportament',
    'section_lines' => 'Línies',
    'section_additional' => 'Addicional',

    // Form labels
    'label_name' => 'Nom',
    'label_name_placeholder' => 'Ex: Manteniment mensual Web',
    'label_client' => 'Client',
    'label_series' => 'Sèrie',
    'label_payment_template' => 'Plantilla de venciment',
    'label_invoice_type' => 'Tipus de factura',
    'label_regime_key' => 'Clau de règim',
    'label_global_discount' => 'Descompte global (%)',
    'label_notes' => 'Notes',
    'label_footer_text' => 'Peu de pàgina',
    'label_start_date' => 'Data d\'inici',
    'label_end_date' => 'Data de fi',
    'label_interval_value' => 'Cada',
    'label_interval_unit' => 'Unitat',
    'label_max_occurrences' => 'Màx. generacions',
    'label_auto_finalize' => 'Auto-finalitzar',
    'label_auto_finalize_help' => 'Les factures es finalitzaran automàticament amb número fiscal. Es registraran a VeriFactu.',
    'label_auto_send_email' => 'Auto-enviar per email',
    'label_auto_send_email_help' => 'Només funciona si auto-finalitzar està actiu i l\'SMTP està configurat.',
    'label_email_recipients' => 'Destinataris email',
    'label_email_recipients_help' => 'Separats per coma. Si es deixa buit, s\'usarà l\'email del client.',
    'label_next_issue_date' => 'Propera emissió',
    'label_occurrences_count' => 'Generades',
    'label_created_by' => 'Creat per',

    // Interval units
    'unit_day' => 'dia(es)',
    'unit_week' => 'setmana(es)',
    'unit_month' => 'mes(os)',
    'unit_year' => 'any(s)',

    // Frequency display
    'frequency_every' => 'Cada :value :unit',

    // Statuses
    'status_active' => 'Activa',
    'status_paused' => 'Pausada',
    'status_finished' => 'Finalitzada',

    // Table columns
    'col_name' => 'Nom',
    'col_client' => 'Client',
    'col_frequency' => 'Freqüència',
    'col_next_date' => 'Propera emissió',
    'col_status' => 'Estat',
    'col_generated' => 'Generades',
    'col_actions' => 'Accions',

    // Actions
    'btn_create' => 'Nova recurrent',
    'btn_save' => 'Desar',
    'btn_update' => 'Actualitzar',
    'btn_edit' => 'Editar',
    'btn_delete' => 'Eliminar',
    'btn_pause' => 'Pausar',
    'btn_resume' => 'Reprendre',
    'btn_generate_now' => 'Generar ara',
    'btn_back' => 'Tornar',

    // Show page
    'show_config' => 'Configuració',
    'show_history' => 'Historial de factures generades',
    'show_no_documents' => 'Encara no s\'ha generat cap factura.',
    'show_document_number' => 'Número',
    'show_document_date' => 'Data',
    'show_document_total' => 'Total',
    'show_document_status' => 'Estat',
    'show_unlimited' => 'Sense límit',

    // Flash messages
    'flash_created' => 'Factura recurrent creada correctament.',
    'flash_updated' => 'Factura recurrent actualitzada correctament.',
    'flash_deleted' => 'Factura recurrent eliminada.',
    'flash_paused' => 'Factura recurrent pausada.',
    'flash_resumed' => 'Factura recurrent represa.',
    'flash_generated' => 'Factura generada: :number',

    // Errors
    'error_cannot_delete_active' => 'No es pot eliminar una recurrent activa que ja ha generat factures. Pausa-la primer.',
    'error_finished' => 'Aquesta recurrent ja ha finalitzat.',
    'error_not_active' => 'Només es poden generar factures de recurrents actives.',
    'error_generation_failed' => 'Error en generar la factura: :error',

    // Confirm dialogs
    'confirm_delete_title' => 'Eliminar recurrent',
    'confirm_delete_message' => 'Estàs segur que vols eliminar la recurrent ":name"?',
    'confirm_generate_title' => 'Generar factura ara',
    'confirm_generate_message' => 'Es generarà una factura immediatament des de la plantilla ":name".',

    // Empty state
    'empty_title' => 'Sense factures recurrents',
    'empty_description' => 'Crea la teva primera factura recurrent per automatitzar la facturació periòdica.',

    // Validation
    'validation_name_required' => 'El nom és obligatori.',
    'validation_client_required' => 'El client és obligatori.',
    'validation_interval_required' => 'L\'interval és obligatori.',
    'validation_start_date_required' => 'La data d\'inici és obligatòria.',
    'validation_lines_required' => 'Cal afegir almenys una línia.',
    'validation_concept_required' => 'El concepte és obligatori a cada línia.',
    'validation_quantity_required' => 'La quantitat és obligatòria.',
    'validation_unit_price_required' => 'El preu unitari és obligatori.',
    'validation_vat_rate_required' => 'El tipus d\'IVA és obligatori.',

    // Badge on document edit
    'badge_from_recurring' => 'Generada des de: :name',
];
