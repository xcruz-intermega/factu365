<?php

return [
    // Index
    'title' => 'Gastos',
    'new_expense' => 'Nuevo gasto',
    'search_placeholder' => 'Buscar concepto o proveedor...',
    'all_categories' => 'Todas las categorías',
    'all_statuses' => 'Todos los estados',
    'no_expenses' => 'No se encontraron gastos.',
    'delete_title' => 'Eliminar gasto',
    'delete_message' => '¿Estás seguro de que quieres eliminar el gasto \':concept\'? Esta acción no se puede deshacer.',
    'mark_as_paid_title' => 'Marcar como pagado',
    'mark_as_paid_confirm' => 'Confirmar pago',
    'mark_as_paid_message' => 'Marca el gasto ":concept" como pagado.',
    'payment_date_label' => 'Fecha de pago *',
    'payment_method_label' => 'Método de pago',
    'pay' => 'Pagar',

    // Columns
    'col_date' => 'Fecha',
    'col_concept' => 'Concepto',
    'col_supplier' => 'Proveedor',
    'col_category' => 'Categoría',
    'col_total' => 'Total',
    'col_status' => 'Estado',

    // Status
    'status_pending' => 'Pendiente',
    'status_paid' => 'Pagado',

    // Create / Edit
    'create_expense' => 'Registrar gasto',
    'edit_expense' => 'Editar gasto',

    // Form sections
    'section_data' => 'Datos del gasto',
    'section_amounts' => 'Importes',
    'section_payment' => 'Pago',
    'section_attachment' => 'Adjunto y notas',

    // Form fields
    'date' => 'Fecha *',
    'category' => 'Categoría',
    'no_category' => '-- Sin categoría --',
    'supplier' => 'Proveedor',
    'search_supplier' => 'Buscar proveedor...',
    'supplier_name' => 'Nombre proveedor',
    'supplier_name_placeholder' => 'Nombre libre del proveedor',
    'invoice_number' => 'Nº factura/ticket',
    'invoice_number_placeholder' => 'Referencia del documento',
    'concept' => 'Concepto *',
    'concept_placeholder' => 'Descripción breve del gasto',
    'tax_base' => 'Base imponible *',
    'vat_pct' => 'IVA %',
    'irpf_pct' => 'IRPF %',
    'irpf_type' => 'Tipo retención',
    'irpf_type_professional' => 'Profesional (M.111)',
    'irpf_type_rental' => 'Alquiler (M.115)',
    'irpf_type_other' => 'Otro',
    'vat_label' => 'IVA: ',
    'irpf_label' => 'IRPF: -',
    'status' => 'Estado',
    'payment_date' => 'Fecha pago',
    'payment_method' => 'Método pago',
    'attachment' => 'Adjunto (PDF, imagen)',
    'attachment_exists' => 'Adjunto actual guardado. Sube uno nuevo para reemplazarlo.',
    'view_attachment' => 'Ver adjunto',
    'download_attachment' => 'Descargar',
    'notes' => 'Notas',
    'notes_placeholder' => 'Notas adicionales',

    // Flash messages
    'flash_created' => 'Gasto registrado correctamente.',
    'flash_updated' => 'Gasto actualizado correctamente.',
    'flash_deleted' => 'Gasto eliminado correctamente.',
    'flash_paid' => 'Gasto marcado como pagado.',

    // Categories CRUD
    'categories_title' => 'Categorías de gastos',
    'categories_section' => 'Categorías',
    'new_category' => 'Nueva categoría',
    'category_name' => 'Nombre',
    'category_code' => 'Código',
    'category_parent' => 'Categoría padre',
    'category_no_parent' => '— Sin padre —',
    'col_category_name' => 'Nombre',
    'col_category_code' => 'Código',
    'col_category_order' => 'Orden',
    'no_categories' => 'No hay categorías de gastos. Crea la primera.',
    'delete_category_title' => 'Eliminar categoría',
    'delete_category_message' => '¿Estás seguro de que quieres eliminar la categoría ":name"?',
    'flash_category_created' => 'Categoría creada correctamente.',
    'flash_category_updated' => 'Categoría actualizada correctamente.',
    'flash_category_deleted' => 'Categoría eliminada correctamente.',
    'error_category_self_parent' => 'Una categoría no puede ser su propio padre.',
    'error_category_has_expenses' => 'No se puede eliminar: la categoría tiene gastos asociados.',
    'error_category_has_children' => 'No se puede eliminar: la categoría tiene subcategorías.',
];
