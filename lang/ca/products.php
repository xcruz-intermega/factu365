<?php

return [
    // Index
    'title' => 'Productes',
    'title_full' => 'Productes i serveis',
    'new_product' => 'Nou producte',
    'search_placeholder' => 'Cercar per nom o referència...',
    'all_types' => 'Tots els tipus',
    'type_products' => 'Productes',
    'type_services' => 'Serveis',
    'all_families' => 'Totes les famílies',
    'no_products' => 'No s\'han trobat productes.',
    'delete_title' => 'Eliminar producte',
    'delete_message' => 'Esteu segur que voleu eliminar \':name\'? Aquesta acció es pot desfer.',
    'type_product' => 'Producte',
    'type_service' => 'Servei',

    // Columns
    'col_ref' => 'Ref.',
    'col_name' => 'Nom',
    'col_family' => 'Família',
    'col_type' => 'Tipus',
    'col_price' => 'Preu',
    'col_vat' => 'IVA',

    // Create / Edit
    'create_product' => 'Crear producte',
    'edit_product' => 'Editar :name',

    // Form sections
    'section_data' => 'Dades del producte',
    'section_pricing' => 'Preu i impostos',
    'section_components' => 'Escandall (components)',

    // Form fields
    'type_label' => 'Tipus *',
    'reference' => 'Referència',
    'reference_placeholder' => 'REF-001',
    'unit_measure' => 'Unitat de mesura',
    'family' => 'Família',
    'no_family' => 'Sense família',
    'name' => 'Nom *',
    'description' => 'Descripció',
    'unit_price' => 'Preu unitari (sense IVA) *',
    'vat_type' => 'Tipus d\'IVA *',
    'exemption_cause' => 'Causa d\'exempció *',
    'apply_irpf' => 'Aplicar retenció IRPF',

    // Exemption codes (product-level, longer labels)
    'exemption_e1' => 'E1 - Exempta per l\'article 20',
    'exemption_e2' => 'E2 - Exempta per l\'article 21',
    'exemption_e3' => 'E3 - Exempta per l\'article 22',
    'exemption_e4' => 'E4 - Exempta per l\'article 23 i 24',
    'exemption_e5' => 'E5 - Exempta per l\'article 25',
    'exemption_e6' => 'E6 - Exempta per altres',

    // Units
    'unit_unidad' => 'Unitat',
    'unit_hora' => 'Hora',
    'unit_dia' => 'Dia',
    'unit_mes' => 'Mes',
    'unit_kg' => 'Quilogram',
    'unit_metro' => 'Metre',
    'unit_m2' => 'Metre²',
    'unit_litro' => 'Litre',
    'unit_pack' => 'Pack',

    // Components / Escandall
    'total_cost' => 'Cost total: ',
    'col_component' => 'Component',
    'col_reference' => 'Referència',
    'col_quantity' => 'Quantitat',
    'col_unit_price' => 'Preu ut.',
    'col_cost' => 'Cost',
    'remove_component' => 'Eliminar component',
    'no_components' => 'Sense components. Producte simple.',
    'add_component' => 'Afegir component',
    'search_product' => 'Cercar producte...',
    'quantity' => 'Quantitat',

    // Flash messages
    'flash_created' => 'Producte creat correctament.',
    'flash_updated' => 'Producte actualitzat correctament.',
    'flash_deleted' => 'Producte eliminat correctament.',
    'flash_component_added' => 'Component afegit.',
    'flash_component_deleted' => 'Component eliminat.',
    'error_component_exists' => 'Aquest component ja està afegit.',

    // Stock
    'section_stock' => 'Inventari',
    'track_stock' => 'Controlar estoc',
    'stock_quantity' => 'Estoc actual',
    'minimum_stock' => 'Estoc mínim',
    'allow_negative_stock' => 'Permetre estoc negatiu',
    'stock_mode' => 'Mode d\'estoc',
    'stock_mode_self' => 'Estoc propi',
    'stock_mode_components' => 'Descomptar de components',
    'col_stock' => 'Estoc',
    'stock_adjust' => 'Ajustar',
    'stock_components' => 'Comp.',

    // Stock movements
    'stock_movements_title' => 'Moviments d\'estoc — :name',
    'movement_type_sale' => 'Venda',
    'movement_type_purchase' => 'Compra',
    'movement_type_adjustment' => 'Ajust',
    'movement_type_return' => 'Devolució',
    'movement_type_initial' => 'Inicial',
    'col_date' => 'Data',
    'col_type' => 'Tipus',
    'col_document' => 'Document',
    'col_movement_qty' => 'Quantitat',
    'col_stock_after' => 'Estoc result.',
    'col_notes' => 'Notes',
    'col_user' => 'Usuari',

    // Manual adjustment
    'adjustment_title' => 'Ajust manual',
    'adjustment_quantity' => 'Quantitat (+/-)',
    'adjustment_notes' => 'Notes',
    'adjustment_placeholder' => 'Motiu de l\'ajust...',
    'flash_adjustment_created' => 'Ajust d\'estoc registrat.',

    // Warnings
    'insufficient_stock' => 'Estoc insuficient per a \':name\' (disponible: :available).',
    'negative_stock_warning' => 'L\'estoc quedarà negatiu',
    'stock_available' => 'Disponible: :qty',

    // Image
    'section_image' => 'Imatge del producte',
    'image_help' => 'JPG, PNG o WebP. Màxim 2 MB.',
    'remove_image' => 'Eliminar imatge',
    'flash_image_deleted' => 'Imatge eliminada.',
    'col_image' => 'Imatge',
    'no_image' => 'Sense imatge',

    // Catalog
    'catalog_title' => 'Catàleg de productes',
    'public_catalog_title' => 'Catàleg',
    'public_catalog_subtitle' => 'Productes i serveis',
    'catalog_pdf_title' => 'Catàleg de productes',
    'download_catalog_pdf' => 'Descarregar catàleg PDF',
    'view_grid' => 'Vista quadrícula',
    'view_table' => 'Vista taula',
    'contact' => 'Contacte',
    'show_prices' => 'Mostrar preus',
    'show_images' => 'Mostrar imatges',
    'no_category' => 'Sense categoria',
];
