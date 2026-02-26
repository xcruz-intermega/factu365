<?php

return [
    // Titles
    'libro_ventas' => 'Libro de Ventas',
    'libro_compras' => 'Libro de Compras',
    'libro_expedidas' => 'Libro de Facturas Expedidas',
    'libro_recibidas' => 'Libro de Facturas Recibidas',

    // Columns common
    'col_date' => 'Fecha',
    'col_number' => 'Número',
    'col_series' => 'Serie',
    'col_client' => 'Cliente',
    'col_supplier' => 'Proveedor',
    'col_nif' => 'NIF',
    'col_base' => 'Base imponible',
    'col_vat' => 'IVA',
    'col_irpf' => 'IRPF',
    'col_total' => 'Total',
    'col_invoice_number' => 'Nº factura',
    'col_origin' => 'Origen',

    // Libro Expedidas (AEAT format)
    'col_invoice_type' => 'Tipo fact.',
    'col_regime' => 'Régimen',
    'col_surcharge' => 'Rec. equiv.',
    'col_name' => 'Nombre/Razón social',

    // Origins
    'origin_document' => 'Documento',
    'origin_expense' => 'Gasto',

    // Monthly subtotals
    'monthly_subtotal' => 'Subtotal :month',

    // Libro Recibidas (AEAT format)
    'col_reception_number' => 'Nº Recep.',
    'col_operation_date' => 'Fecha operación',
    'col_regime_key' => 'Clave régimen',

    // Invoice types (AEAT)
    'invoice_type_F1' => 'F1 - Factura',
    'invoice_type_F2' => 'F2 - Factura simplificada',
    'invoice_type_F3' => 'F3 - Factura emitida sustitutiva de simplificada',
    'invoice_type_R1' => 'R1 - Rectificativa',
    'invoice_type_R2' => 'R2 - Rectificativa (art. 80.3)',
    'invoice_type_R3' => 'R3 - Rectificativa (art. 80.4)',
    'invoice_type_R4' => 'R4 - Rectificativa (otros)',
    'invoice_type_R5' => 'R5 - Rectificativa simplificada',

    // Regime keys (AEAT)
    'regime_01' => '01 - Régimen general',
    'regime_02' => '02 - Exportaciones',
    'regime_03' => '03 - Bienes usados',
    'regime_05' => '05 - Régimen especial agencias viaje',
    'regime_07' => '07 - Régimen especial criterio de caja',

    // AEAT export
    'export_aeat_csv' => 'CSV AEAT',
    'aeat_csv_tooltip' => 'Formato oficial AEAT (1 fila por tipo IVA)',

    // Totals
    'total' => 'Total',
    'total_period' => 'Total del periodo',

    // No data
    'no_data' => 'Sin datos para el periodo seleccionado.',

    // VAT breakdown
    'vat_breakdown' => 'Desglose IVA',
    'vat_rate' => 'Tipo',
    'vat_base' => 'Base',
    'vat_amount' => 'Cuota',

    // PDF
    'pdf_period_label' => 'Periodo: :from a :to',
];
