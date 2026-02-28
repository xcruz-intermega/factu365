<?php

return [
    // Page titles
    'title_index' => 'Facturas Recurrentes',
    'title_create' => 'Nueva Recurrente',
    'title_edit' => 'Editar Recurrente',
    'title_show' => 'Detalle Recurrente',

    // Form sections
    'section_info' => 'Información',
    'section_recurrence' => 'Recurrencia',
    'section_behavior' => 'Comportamiento',
    'section_lines' => 'Líneas',
    'section_additional' => 'Adicional',

    // Form labels
    'label_name' => 'Nombre',
    'label_name_placeholder' => 'Ej: Mantenimiento mensual Web',
    'label_client' => 'Cliente',
    'label_series' => 'Serie',
    'label_payment_template' => 'Plantilla de vencimiento',
    'label_invoice_type' => 'Tipo de factura',
    'label_regime_key' => 'Clave de régimen',
    'label_global_discount' => 'Descuento global (%)',
    'label_notes' => 'Notas',
    'label_footer_text' => 'Pie de página',
    'label_start_date' => 'Fecha de inicio',
    'label_end_date' => 'Fecha de fin',
    'label_interval_value' => 'Cada',
    'label_interval_unit' => 'Unidad',
    'label_max_occurrences' => 'Máx. generaciones',
    'label_auto_finalize' => 'Auto-finalizar',
    'label_auto_finalize_help' => 'Las facturas se finalizarán automáticamente con número fiscal. Se registrarán en VeriFactu.',
    'label_auto_send_email' => 'Auto-enviar por email',
    'label_auto_send_email_help' => 'Solo funciona si auto-finalizar está activo y el SMTP está configurado.',
    'label_email_recipients' => 'Destinatarios email',
    'label_email_recipients_help' => 'Separados por coma. Si se deja vacío, se usará el email del cliente.',
    'label_next_issue_date' => 'Próxima emisión',
    'label_occurrences_count' => 'Generadas',
    'label_created_by' => 'Creado por',

    // Interval units
    'unit_day' => 'día(s)',
    'unit_week' => 'semana(s)',
    'unit_month' => 'mes(es)',
    'unit_year' => 'año(s)',

    // Frequency display
    'frequency_every' => 'Cada :value :unit',

    // Statuses
    'status_active' => 'Activa',
    'status_paused' => 'Pausada',
    'status_finished' => 'Finalizada',

    // Table columns
    'col_name' => 'Nombre',
    'col_client' => 'Cliente',
    'col_frequency' => 'Frecuencia',
    'col_next_date' => 'Próxima emisión',
    'col_status' => 'Estado',
    'col_generated' => 'Generadas',
    'col_actions' => 'Acciones',

    // Actions
    'btn_create' => 'Nueva recurrente',
    'btn_save' => 'Guardar',
    'btn_update' => 'Actualizar',
    'btn_edit' => 'Editar',
    'btn_delete' => 'Eliminar',
    'btn_pause' => 'Pausar',
    'btn_resume' => 'Reanudar',
    'btn_generate_now' => 'Generar ahora',
    'btn_back' => 'Volver',

    // Show page
    'show_config' => 'Configuración',
    'show_history' => 'Historial de facturas generadas',
    'show_no_documents' => 'Aún no se ha generado ninguna factura.',
    'show_document_number' => 'Número',
    'show_document_date' => 'Fecha',
    'show_document_total' => 'Total',
    'show_document_status' => 'Estado',
    'show_unlimited' => 'Sin límite',

    // Flash messages
    'flash_created' => 'Factura recurrente creada correctamente.',
    'flash_updated' => 'Factura recurrente actualizada correctamente.',
    'flash_deleted' => 'Factura recurrente eliminada.',
    'flash_paused' => 'Factura recurrente pausada.',
    'flash_resumed' => 'Factura recurrente reanudada.',
    'flash_generated' => 'Factura generada: :number',

    // Errors
    'error_cannot_delete_active' => 'No se puede eliminar una recurrente activa que ya ha generado facturas. Páusala primero.',
    'error_finished' => 'Esta recurrente ya ha finalizado.',
    'error_not_active' => 'Solo se pueden generar facturas de recurrentes activas.',
    'error_generation_failed' => 'Error al generar la factura: :error',

    // Confirm dialogs
    'confirm_delete_title' => 'Eliminar recurrente',
    'confirm_delete_message' => '¿Estás seguro de que quieres eliminar la recurrente ":name"?',
    'confirm_generate_title' => 'Generar factura ahora',
    'confirm_generate_message' => 'Se generará una factura inmediatamente desde la plantilla ":name".',

    // Empty state
    'empty_title' => 'Sin facturas recurrentes',
    'empty_description' => 'Crea tu primera factura recurrente para automatizar la facturación periódica.',

    // Validation
    'validation_name_required' => 'El nombre es obligatorio.',
    'validation_client_required' => 'El cliente es obligatorio.',
    'validation_interval_required' => 'El intervalo es obligatorio.',
    'validation_start_date_required' => 'La fecha de inicio es obligatoria.',
    'validation_lines_required' => 'Debe añadir al menos una línea.',
    'validation_concept_required' => 'El concepto es obligatorio en cada línea.',
    'validation_quantity_required' => 'La cantidad es obligatoria.',
    'validation_unit_price_required' => 'El precio unitario es obligatorio.',
    'validation_vat_rate_required' => 'El tipo de IVA es obligatorio.',

    // Badge on document edit
    'badge_from_recurring' => 'Generada desde: :name',
];
