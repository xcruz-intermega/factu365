<?php

return [
    // Index
    'title' => 'Products',
    'title_full' => 'Products and services',
    'new_product' => 'New product',
    'search_placeholder' => 'Search by name or reference...',
    'all_types' => 'All types',
    'type_products' => 'Products',
    'type_services' => 'Services',
    'all_families' => 'All families',
    'no_products' => 'No products found.',
    'delete_title' => 'Delete product',
    'delete_message' => 'Are you sure you want to delete \':name\'? This action can be undone.',
    'type_product' => 'Product',
    'type_service' => 'Service',

    // Columns
    'col_ref' => 'Ref.',
    'col_name' => 'Name',
    'col_family' => 'Family',
    'col_type' => 'Type',
    'col_price' => 'Price',
    'col_vat' => 'VAT',

    // Create / Edit
    'create_product' => 'Create product',
    'edit_product' => 'Edit :name',

    // Form sections
    'section_data' => 'Product details',
    'section_pricing' => 'Price and taxes',
    'section_components' => 'Bill of materials (components)',

    // Form fields
    'type_label' => 'Type *',
    'reference' => 'Reference',
    'reference_placeholder' => 'REF-001',
    'unit_measure' => 'Unit of measure',
    'family' => 'Family',
    'no_family' => 'No family',
    'name' => 'Name *',
    'description' => 'Description',
    'unit_price' => 'Unit price (excl. VAT) *',
    'vat_type' => 'VAT rate *',
    'exemption_cause' => 'Exemption reason *',
    'apply_irpf' => 'Apply IRPF withholding',

    // Exemption codes (product-level, longer labels)
    'exemption_e1' => 'E1 - Exempt under Article 20',
    'exemption_e2' => 'E2 - Exempt under Article 21',
    'exemption_e3' => 'E3 - Exempt under Article 22',
    'exemption_e4' => 'E4 - Exempt under Articles 23 and 24',
    'exemption_e5' => 'E5 - Exempt under Article 25',
    'exemption_e6' => 'E6 - Exempt other',

    // Units
    'unit_unidad' => 'Unit',
    'unit_hora' => 'Hour',
    'unit_dia' => 'Day',
    'unit_mes' => 'Month',
    'unit_kg' => 'Kilogram',
    'unit_metro' => 'Metre',
    'unit_m2' => 'Square metre',
    'unit_litro' => 'Litre',
    'unit_pack' => 'Pack',

    // Components / Bill of materials
    'total_cost' => 'Total cost: ',
    'col_component' => 'Component',
    'col_reference' => 'Reference',
    'col_quantity' => 'Quantity',
    'col_unit_price' => 'Unit price',
    'col_cost' => 'Cost',
    'remove_component' => 'Remove component',
    'no_components' => 'No components. Simple product.',
    'add_component' => 'Add component',
    'search_product' => 'Search product...',
    'quantity' => 'Quantity',

    // Flash messages
    'flash_created' => 'Product created successfully.',
    'flash_updated' => 'Product updated successfully.',
    'flash_deleted' => 'Product deleted successfully.',
    'flash_component_added' => 'Component added.',
    'flash_component_deleted' => 'Component removed.',
    'error_component_exists' => 'This component has already been added.',

    // Stock
    'section_stock' => 'Inventory',
    'track_stock' => 'Track stock',
    'stock_quantity' => 'Current stock',
    'minimum_stock' => 'Minimum stock',
    'allow_negative_stock' => 'Allow negative stock',
    'stock_mode' => 'Stock mode',
    'stock_mode_self' => 'Own stock',
    'stock_mode_components' => 'Deduct from components',
    'col_stock' => 'Stock',
    'stock_adjust' => 'Adjust',
    'stock_components' => 'Comp.',

    // Stock movements
    'stock_movements_title' => 'Stock movements — :name',
    'movement_type_sale' => 'Sale',
    'movement_type_purchase' => 'Purchase',
    'movement_type_adjustment' => 'Adjustment',
    'movement_type_return' => 'Return',
    'movement_type_initial' => 'Initial',
    'col_date' => 'Date',
    'col_type' => 'Type',
    'col_document' => 'Document',
    'col_movement_qty' => 'Quantity',
    'col_stock_after' => 'Stock after',
    'col_notes' => 'Notes',
    'col_user' => 'User',

    // Manual adjustment
    'adjustment_title' => 'Manual adjustment',
    'adjustment_quantity' => 'Quantity (+/-)',
    'adjustment_notes' => 'Notes',
    'adjustment_placeholder' => 'Reason for adjustment...',
    'flash_adjustment_created' => 'Stock adjustment recorded.',

    // Warnings
    'insufficient_stock' => 'Insufficient stock for \':name\' (available: :available).',
    'negative_stock_warning' => 'Stock will go negative',
    'stock_available' => 'Available: :qty',
];
