<?php

return [
    // Index
    'title' => 'Productos',
    'title_full' => 'Productos y servicios',
    'new_product' => 'Nuevo producto',
    'search_placeholder' => 'Buscar por nombre o referencia...',
    'all_types' => 'Todos los tipos',
    'type_products' => 'Productos',
    'type_services' => 'Servicios',
    'all_families' => 'Todas las familias',
    'no_products' => 'No se encontraron productos.',
    'delete_title' => 'Eliminar producto',
    'delete_message' => '¿Estás seguro de que quieres eliminar \':name\'? Esta acción se puede deshacer.',
    'type_product' => 'Producto',
    'type_service' => 'Servicio',

    // Columns
    'col_ref' => 'Ref.',
    'col_name' => 'Nombre',
    'col_family' => 'Familia',
    'col_type' => 'Tipo',
    'col_price' => 'Precio',
    'col_vat' => 'IVA',

    // Create / Edit
    'create_product' => 'Crear producto',
    'edit_product' => 'Editar :name',

    // Form sections
    'section_data' => 'Datos del producto',
    'section_pricing' => 'Precio e impuestos',
    'section_components' => 'Escandallo (componentes)',

    // Form fields
    'type_label' => 'Tipo *',
    'reference' => 'Referencia',
    'reference_placeholder' => 'REF-001',
    'unit_measure' => 'Unidad de medida',
    'family' => 'Familia',
    'no_family' => 'Sin familia',
    'name' => 'Nombre *',
    'description' => 'Descripción',
    'unit_price' => 'Precio unitario (sin IVA) *',
    'vat_type' => 'Tipo de IVA *',
    'exemption_cause' => 'Causa de exención *',
    'apply_irpf' => 'Aplicar retención IRPF',

    // Exemption codes (product-level, longer labels)
    'exemption_e1' => 'E1 - Exenta por el artículo 20',
    'exemption_e2' => 'E2 - Exenta por el artículo 21',
    'exemption_e3' => 'E3 - Exenta por el artículo 22',
    'exemption_e4' => 'E4 - Exenta por el artículo 23 y 24',
    'exemption_e5' => 'E5 - Exenta por el artículo 25',
    'exemption_e6' => 'E6 - Exenta por otros',

    // Units
    'unit_unidad' => 'Unidad',
    'unit_hora' => 'Hora',
    'unit_dia' => 'Día',
    'unit_mes' => 'Mes',
    'unit_kg' => 'Kilogramo',
    'unit_metro' => 'Metro',
    'unit_m2' => 'Metro²',
    'unit_litro' => 'Litro',
    'unit_pack' => 'Pack',

    // Components / Escandallo
    'total_cost' => 'Coste total: ',
    'col_component' => 'Componente',
    'col_reference' => 'Referencia',
    'col_quantity' => 'Cantidad',
    'col_unit_price' => 'Precio ud.',
    'col_cost' => 'Coste',
    'remove_component' => 'Eliminar componente',
    'no_components' => 'Sin componentes. Producto simple.',
    'add_component' => 'Añadir componente',
    'search_product' => 'Buscar producto...',
    'quantity' => 'Cantidad',

    // Flash messages
    'flash_created' => 'Producto creado correctamente.',
    'flash_updated' => 'Producto actualizado correctamente.',
    'flash_deleted' => 'Producto eliminado correctamente.',
    'flash_component_added' => 'Componente añadido.',
    'flash_component_deleted' => 'Componente eliminado.',
    'error_component_exists' => 'Este componente ya está añadido.',

    // Stock
    'section_stock' => 'Inventario',
    'track_stock' => 'Controlar stock',
    'stock_quantity' => 'Stock actual',
    'minimum_stock' => 'Stock mínimo',
    'allow_negative_stock' => 'Permitir stock negativo',
    'stock_mode' => 'Modo de stock',
    'stock_mode_self' => 'Stock propio',
    'stock_mode_components' => 'Descontar de componentes',
    'col_stock' => 'Stock',
    'stock_adjust' => 'Ajustar',
    'stock_components' => 'Comp.',

    // Stock movements
    'stock_movements_title' => 'Movimientos de stock — :name',
    'movement_type_sale' => 'Venta',
    'movement_type_purchase' => 'Compra',
    'movement_type_adjustment' => 'Ajuste',
    'movement_type_return' => 'Devolución',
    'movement_type_initial' => 'Inicial',
    'col_date' => 'Fecha',
    'col_type' => 'Tipo',
    'col_document' => 'Documento',
    'col_movement_qty' => 'Cantidad',
    'col_stock_after' => 'Stock result.',
    'col_notes' => 'Notas',
    'col_user' => 'Usuario',

    // Manual adjustment
    'adjustment_title' => 'Ajuste manual',
    'adjustment_quantity' => 'Cantidad (+/-)',
    'adjustment_notes' => 'Notas',
    'adjustment_placeholder' => 'Motivo del ajuste...',
    'flash_adjustment_created' => 'Ajuste de stock registrado.',

    // Warnings
    'insufficient_stock' => 'Stock insuficiente para \':name\' (disponible: :available).',
    'negative_stock_warning' => 'El stock quedará negativo',
    'stock_available' => 'Disponible: :qty',

    // Image
    'section_image' => 'Imagen del producto',
    'image_help' => 'JPG, PNG o WebP. Máximo 2 MB.',
    'remove_image' => 'Eliminar imagen',
    'flash_image_deleted' => 'Imagen eliminada.',
    'col_image' => 'Imagen',
    'no_image' => 'Sin imagen',

    // Catalog
    'catalog_title' => 'Catálogo de productos',
    'public_catalog_title' => 'Catálogo',
    'public_catalog_subtitle' => 'Productos y servicios',
    'catalog_pdf_title' => 'Catálogo de productos',
    'download_catalog_pdf' => 'Descargar catálogo PDF',
    'view_grid' => 'Vista cuadrícula',
    'view_table' => 'Vista tabla',
    'contact' => 'Contacto',
    'show_prices' => 'Mostrar precios',
    'show_images' => 'Mostrar imágenes',
    'no_category' => 'Sin categoría',
];
