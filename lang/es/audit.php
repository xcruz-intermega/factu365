<?php

return [
    // Page
    'title' => 'Registro de auditoría',
    'description' => 'Historial completo de operaciones realizadas en el sistema.',
    'no_logs' => 'No hay registros de auditoría.',

    // Filters
    'filter_action' => 'Acción',
    'filter_model' => 'Entidad',
    'filter_user' => 'Usuario',
    'filter_from' => 'Desde',
    'filter_to' => 'Hasta',
    'filter_search' => 'Buscar...',
    'filter_all_actions' => 'Todas las acciones',
    'filter_all_models' => 'Todas las entidades',
    'filter_all_users' => 'Todos los usuarios',
    'filter_clear' => 'Limpiar filtros',

    // Columns
    'col_date' => 'Fecha',
    'col_user' => 'Usuario',
    'col_action' => 'Acción',
    'col_model' => 'Entidad',
    'col_entity_id' => 'ID',
    'col_summary' => 'Resumen',
    'col_ip' => 'IP',
    'col_field' => 'Campo',
    'col_old_value' => 'Valor anterior',
    'col_new_value' => 'Valor nuevo',

    // Actions
    'action_created' => 'Creado',
    'action_updated' => 'Modificado',
    'action_deleted' => 'Eliminado',
    'action_finalized' => 'Finalizado',
    'action_sent_to_aeat' => 'Enviado a AEAT',
    'action_marked_paid' => 'Marcado pagado',
    'action_status_changed' => 'Estado cambiado',
    'action_converted' => 'Convertido',
    'action_restored' => 'Restaurado',

    // Models
    'model_document' => 'Documento',
    'model_document_line' => 'Línea de documento',
    'model_document_series' => 'Serie de documento',
    'model_document_due_date' => 'Vencimiento',
    'model_expense' => 'Gasto',
    'model_expense_category' => 'Categoría de gasto',
    'model_invoicing_record' => 'Registro VeriFactu',
    'model_aeat_submission' => 'Envío AEAT',
    'model_client' => 'Cliente',
    'model_product' => 'Producto',
    'model_product_family' => 'Familia de producto',
    'model_product_component' => 'Componente de producto',
    'model_client_discount' => 'Descuento de cliente',
    'model_company_profile' => 'Perfil de empresa',
    'model_certificate' => 'Certificado',
    'model_pdf_template' => 'Plantilla PDF',
    'model_payment_template' => 'Plantilla de pago',
    'model_payment_template_line' => 'Línea de plantilla de pago',
    'model_stock_movement' => 'Movimiento de stock',
    'model_user' => 'Usuario',

    // Misc
    'system' => 'Sistema',
    'redacted' => '[OCULTO]',
    'null_value' => '(vacío)',
    'expand' => 'Ver detalle',
    'collapse' => 'Ocultar detalle',
    'export_csv' => 'Exportar CSV',
];
