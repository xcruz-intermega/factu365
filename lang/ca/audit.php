<?php

return [
    // Page
    'title' => "Registre d'auditoria",
    'description' => 'Historial complet de les operacions realitzades al sistema.',
    'no_logs' => "No hi ha registres d'auditoria.",

    // Filters
    'filter_action' => 'Acció',
    'filter_model' => 'Entitat',
    'filter_user' => 'Usuari',
    'filter_from' => 'Des de',
    'filter_to' => 'Fins a',
    'filter_search' => 'Cercar...',
    'filter_all_actions' => 'Totes les accions',
    'filter_all_models' => 'Totes les entitats',
    'filter_all_users' => 'Tots els usuaris',
    'filter_clear' => 'Netejar filtres',

    // Columns
    'col_date' => 'Data',
    'col_user' => 'Usuari',
    'col_action' => 'Acció',
    'col_model' => 'Entitat',
    'col_entity_id' => 'ID',
    'col_summary' => 'Resum',
    'col_ip' => 'IP',
    'col_field' => 'Camp',
    'col_old_value' => 'Valor anterior',
    'col_new_value' => 'Valor nou',

    // Actions
    'action_created' => 'Creat',
    'action_updated' => 'Modificat',
    'action_deleted' => 'Eliminat',
    'action_finalized' => 'Finalitzat',
    'action_sent_to_aeat' => 'Enviat a AEAT',
    'action_marked_paid' => 'Marcat com a pagat',
    'action_status_changed' => "Estat canviat",
    'action_converted' => 'Convertit',
    'action_restored' => 'Restaurat',

    // Models
    'model_document' => 'Document',
    'model_document_line' => 'Línia de document',
    'model_document_series' => 'Sèrie de document',
    'model_document_due_date' => 'Venciment',
    'model_expense' => 'Despesa',
    'model_expense_category' => 'Categoria de despesa',
    'model_invoicing_record' => 'Registre VeriFactu',
    'model_aeat_submission' => 'Enviament AEAT',
    'model_client' => 'Client',
    'model_product' => 'Producte',
    'model_product_family' => 'Família de producte',
    'model_product_component' => 'Component de producte',
    'model_client_discount' => 'Descompte de client',
    'model_company_profile' => "Perfil d'empresa",
    'model_certificate' => 'Certificat',
    'model_pdf_template' => 'Plantilla PDF',
    'model_payment_template' => 'Plantilla de pagament',
    'model_payment_template_line' => 'Línia de plantilla de pagament',
    'model_stock_movement' => "Moviment d'estoc",
    'model_user' => 'Usuari',

    // Misc
    'system' => 'Sistema',
    'redacted' => '[OCULT]',
    'null_value' => '(buit)',
    'expand' => 'Veure detall',
    'collapse' => 'Amagar detall',
    'export_csv' => 'Exportar CSV',
];
