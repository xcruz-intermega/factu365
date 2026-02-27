<?php

return [
    // Document type labels
    'type_invoice' => 'Factura',
    'type_quote' => 'Presupuesto',
    'type_delivery_note' => 'Albarán',
    'type_rectificative' => 'Rectificativa',
    'type_purchase_invoice' => 'Factura recibida',

    // Lowercase for conversion labels
    'to_invoice' => 'factura',
    'to_delivery_note' => 'albarán',
    'to_quote' => 'presupuesto',

    // Index
    'col_number' => 'Número',
    'col_client' => 'Cliente',
    'col_date' => 'Fecha',
    'col_total' => 'Total',
    'col_status' => 'Estado',
    'search_placeholder' => 'Buscar por número o cliente...',
    'all_statuses' => 'Todos los estados',
    'delete_title' => 'Eliminar documento',

    // Per-type titles (gender-aware)
    'title_invoice' => 'Facturas',
    'title_quote' => 'Presupuestos',
    'title_delivery_note' => 'Albaranes',
    'title_rectificative' => 'Rectificativas',
    'title_purchase_invoice' => 'Facturas recibidas',
    'new_invoice' => 'Nueva factura',
    'new_quote' => 'Nuevo presupuesto',
    'new_delivery_note' => 'Nuevo albarán',
    'new_rectificative' => 'Nueva rectificativa',
    'new_purchase_invoice' => 'Nueva factura recibida',
    'delete_message' => '¿Estás seguro de que quieres eliminar este documento? Esta acción no se puede deshacer.',

    // Create / Edit
    'edit_header' => ':type :number',

    // Actions bar
    'actions_label' => 'Acciones:',
    'mark_sent' => 'Marcar enviado',
    'mark_sent_f' => 'Marcar enviada',
    'accept' => 'Aceptar',
    'reject' => 'Rechazar',
    'mark_paid' => 'Marcar pagada',
    'partial_payment' => 'Pago parcial',
    'mark_overdue' => 'Marcar vencida',
    'cancel_doc' => 'Anular',
    'convert_to' => 'Convertir a :target',
    'create_rectificative' => 'Crear rectificativa',
    'finalize_document' => 'Finalizar :type',

    // Finalize dialog
    'finalize_title' => 'Finalizar documento',
    'finalize_message' => 'Al finalizar se asignará un número de serie y el documento no podrá ser editado. ¿Continuar?',

    // Email dialog
    'email_title' => 'Enviar documento por email',
    'email_to' => 'Destinatario *',
    'email_to_placeholder' => 'email@ejemplo.com',
    'email_subject' => 'Asunto',
    'email_message' => 'Mensaje',
    'email_default_message' => "Estimado cliente,\n\nAdjunto encontrará el documento :type :number.\n\nUn saludo.",
    'email_attachments' => 'Adjuntos',
    'email_attach_pdf' => 'PDF',
    'email_attach_facturae' => 'FacturaE (XML)',
    'email_attach_both' => 'Ambos (PDF + FacturaE)',

    // Read-only summary
    'issue_date' => 'Fecha emisión',

    // Lines table (read-only in Edit)
    'col_concept' => 'Concepto',
    'col_quantity' => 'Cant.',
    'col_price' => 'Precio',

    // Due dates section
    'due_dates' => 'Vencimientos',
    'col_percent' => '%',
    'col_amount' => 'Importe',
    'col_action' => 'Acción',
    'paid' => 'Pagado',
    'unmark' => 'Desmarcar',
    'mark_due_paid' => 'Marcar pagado',

    // Form
    'general_data' => 'Datos generales',
    'client' => 'Cliente',
    'quote_title' => 'Título del presupuesto',
    'quote_title_placeholder' => 'Ej: Presupuesto para Jardinería 400h',
    'invoice_type' => 'Tipo factura',
    'series' => 'Serie',
    'issue_date_label' => 'Fecha emisión *',
    'due_date' => 'Fecha vencimiento',
    'operation_date' => 'Fecha operación',
    'rectification_method' => 'Método rectificación',
    'rectification_substitution' => 'Por sustitución',
    'rectification_differences' => 'Por diferencias',
    'search_client' => 'Buscar cliente...',

    // Due dates form
    'select_template' => 'Seleccionar plantilla...',
    'add_due_date' => '+ Añadir',
    'no_due_dates' => 'Sin vencimientos configurados. Se usará la fecha de vencimiento simple.',

    // Notes
    'notes_footer' => 'Notas y pie',
    'internal_notes' => 'Notas internas',
    'document_footer' => 'Pie de documento',
    'internal_notes_placeholder' => 'Notas internas (no se muestran al cliente)',
    'footer_placeholder' => 'Texto visible en el pie del documento',

    // Submit
    'create_document' => 'Crear :type',
    'create_draft' => 'Crear borrador',

    // Document type labels (full)
    'type_proforma' => 'Proforma',
    'type_receipt' => 'Recibo',

    // Lines editor
    'document_lines' => 'Líneas del documento',
    'add_line' => 'Añadir línea',
    'add_another_line' => 'Añadir otra línea',
    'no_lines' => 'No hay líneas. Haz clic en "Añadir línea" para empezar.',
    'product' => 'Producto',
    'concept_required' => 'Concepto *',
    'concept_placeholder' => 'Concepto de la línea',
    'quantity_required' => 'Cantidad *',
    'unit_price_required' => 'Precio unitario *',
    'unit' => 'Unidad',
    'unit_short' => 'ud.',
    'discount_pct' => 'Dto. %',
    'client_discount' => 'Dto. cliente',
    'vat_pct' => 'IVA %',
    'exemption_required' => 'Exención *',
    'irpf_pct' => 'IRPF %',
    'surcharge_label' => 'R.E.',
    'search_product' => 'Buscar producto...',
    'remove_line' => 'Eliminar línea',
    'components_count' => ':count comp.',
    'expand_components' => 'Expandir componentes (:count)',
    'expand_components_help' => 'Reemplaza esta línea por sus componentes individuales',

    // Totals summary
    'summary' => 'Resumen',
    'line_discounts' => 'Descuentos línea',
    'global_discount' => 'Dto. global',

    // Flash messages (per-type, gender-aware)
    'flash_created_invoice' => 'Factura creada correctamente.',
    'flash_created_quote' => 'Presupuesto creado correctamente.',
    'flash_created_delivery_note' => 'Albarán creado correctamente.',
    'flash_created_rectificative' => 'Rectificativa creada correctamente.',
    'flash_created_purchase_invoice' => 'Factura recibida creada correctamente.',
    'flash_updated_invoice' => 'Factura actualizada correctamente.',
    'flash_updated_quote' => 'Presupuesto actualizado correctamente.',
    'flash_updated_delivery_note' => 'Albarán actualizado correctamente.',
    'flash_updated_rectificative' => 'Rectificativa actualizada correctamente.',
    'flash_updated_purchase_invoice' => 'Factura recibida actualizada correctamente.',
    'flash_deleted_invoice' => 'Factura eliminada correctamente.',
    'flash_deleted_quote' => 'Presupuesto eliminado correctamente.',
    'flash_deleted_delivery_note' => 'Albarán eliminado correctamente.',
    'flash_deleted_rectificative' => 'Rectificativa eliminada correctamente.',
    'flash_deleted_purchase_invoice' => 'Factura recibida eliminada correctamente.',
    'flash_finalized_invoice' => 'Factura finalizada con número :number.',
    'flash_finalized_rectificative' => 'Rectificativa finalizada con número :number.',
    'flash_finalized_purchase_invoice' => 'Factura recibida finalizada con número :number.',
    'flash_converted' => 'Documento convertido a :type.',
    'flash_rectificative_created' => 'Factura rectificativa creada como borrador.',
    'flash_status_updated' => 'Estado actualizado a :status.',
    'flash_email_sent' => 'Documento enviado por email a :email.',
    'flash_due_date_paid' => 'Vencimiento marcado como pagado.',
    'flash_due_date_unpaid' => 'Vencimiento desmarcado.',
    'flash_email_body' => 'Adjunto encontrará el documento :type :number.',
    'error_cannot_edit' => 'Este documento no puede ser editado.',
    'error_no_finalize_needed' => 'Los presupuestos y albaranes no requieren finalización.',
    'error_cannot_finalize' => 'Este documento no puede ser finalizado. Verifique que tiene cliente y líneas.',
    'error_cannot_delete' => 'Este documento no puede ser eliminado.',
    'error_cannot_convert' => 'Este documento no puede ser convertido.',
    'error_invalid_conversion' => 'Tipo de conversión no válido.',
    'error_only_finalized' => 'Solo se pueden rectificar facturas finalizadas.',
    'error_draft_status' => 'No se puede cambiar el estado de un borrador.',
    'error_invalid_status' => 'Estado no válido.',
    'error_draft_email' => 'No se puede enviar un documento en borrador.',

    // E-Invoice Import
    'import_title' => 'Importar factura electrónica',
    'import_drop_zone' => 'Arrastra aquí tu factura electrónica',
    'import_formats' => 'Formatos soportados: FacturaE (XML), Factur-X (PDF)',
    'import_select_file' => 'Seleccionar archivo',
    'import_validation_errors' => 'Se encontraron errores de validación',
    'import_error_no_number' => 'No se encontró el número de factura en el archivo.',
    'import_error_no_nif' => 'No se encontró el NIF del emisor en el archivo.',
    'import_error_totals_mismatch' => 'Los totales calculados no coinciden con los del archivo.',
    'import_error_own_nif' => 'El NIF del emisor coincide con el de tu empresa. No puedes importar tus propias facturas.',
    'import_error_no_xml_in_pdf' => 'No se encontró XML embebido en el PDF. El archivo no es un Factur-X válido.',
    'import_error_unsupported_format' => 'Formato de archivo no soportado. Usa XML (FacturaE) o PDF (Factur-X).',
    'import_supplier_info' => 'Datos del proveedor',
    'import_supplier_name' => 'Nombre',
    'import_link_supplier' => 'Vincular con proveedor existente',
    'import_invoice_info' => 'Datos de la factura',
    'import_invoice_number' => 'Número de factura',
    'import_issue_date' => 'Fecha de emisión',
    'import_lines' => 'Líneas',
    'import_upload_another' => 'Subir otro archivo',
    'import_button' => 'Importar factura',
    'import_success' => 'Factura importada correctamente.',

    // Accounted
    'col_accounted' => 'Contab.',
    'all_accounted' => 'Contabilización',
    'filter_accounted' => 'Contabilizada',
    'filter_not_accounted' => 'No contabilizada',
    'flash_accounted' => 'Documento marcado como contabilizado.',
    'flash_unaccounted' => 'Documento desmarcado como contabilizado.',
    'error_not_accountable' => 'Este tipo de documento no admite contabilización.',

    // FacturaE export
    'download_facturae' => 'Descargar FacturaE',
    'error_facturae_type' => 'Solo se pueden exportar facturas y rectificativas como FacturaE.',
    'error_facturae_draft' => 'Finalice el documento antes de exportar como FacturaE.',

    // FacturaE bulk export
    'export_facturae_title' => 'Exportar FacturaE',
    'export_facturae_subtitle' => 'Exportación masiva en formato FacturaE 3.2.2 XML',
    'export_date_from' => 'Fecha desde',
    'export_date_to' => 'Fecha hasta',
    'export_all_statuses' => 'Todos los estados',
    'export_all_clients' => 'Todos los clientes',
    'export_filter' => 'Filtrar',
    'export_count' => ':count documentos encontrados',
    'export_download_zip' => 'Descargar ZIP FacturaE (:count docs)',
    'export_no_results' => 'No hay documentos que coincidan con los filtros',
    'export_error_empty' => 'No hay documentos para exportar',
    'export_error_limit' => 'Máximo 500 documentos por exportación',
    'error_email_failed' => 'No se pudo enviar el email. Inténtelo de nuevo más tarde.',
];
