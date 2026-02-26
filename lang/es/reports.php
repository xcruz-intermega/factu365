<?php

return [
    // Sales by client
    'sales_by_client' => 'Ventas por cliente',
    'col_client' => 'Cliente',
    'col_nif' => 'NIF',
    'col_invoices' => 'Facturas',
    'no_data' => 'Sin datos para el periodo seleccionado.',

    // Sales by product
    'sales_by_product' => 'Ventas por producto',
    'sales_by_product_full' => 'Ventas por producto/servicio',
    'col_product_concept' => 'Producto/Concepto',
    'col_quantity' => 'Cantidad',

    // Sales by period
    'sales_by_period' => 'Ventas por periodo',
    'group_by' => 'Agrupar por',
    'group_month' => 'Mes',
    'group_quarter' => 'Trimestre',
    'chart_billing' => 'Facturación',
    'col_period' => 'Periodo',

    // Modelo 303
    'modelo_303' => 'Modelo 303',
    'modelo_303_full' => 'Modelo 303 - IVA trimestral',
    'fiscal_year' => 'Ejercicio',
    'quarter' => 'Trimestre',
    'q1_months' => 'Enero - Marzo',
    'q2_months' => 'Abril - Junio',
    'q3_months' => 'Julio - Septiembre',
    'q4_months' => 'Octubre - Diciembre',
    'declarant' => 'Declarante:',
    'period' => 'Periodo:',
    'vat_charged' => 'IVA devengado (repercutido)',
    'issued_invoices' => 'Facturas emitidas',
    'vat_type' => 'Tipo IVA',
    'col_base' => 'Base',
    'col_amount' => 'Cuota',
    'no_operations' => 'Sin operaciones',
    'total_vat_charged' => 'Total IVA devengado',
    'vat_deductible' => 'IVA deducible (soportado)',
    'received_invoices' => 'Facturas recibidas y gastos',
    'total_vat_deductible' => 'Total IVA deducible',
    'settlement_result' => 'Resultado liquidación',
    'difference' => 'Diferencia (a ingresar / a devolver)',
    'fiscal_disclaimer' => '* Este es un borrador orientativo. Revise los datos con su asesor fiscal antes de presentar el modelo oficial.',

    // Modelo 130
    'modelo_130' => 'Modelo 130',
    'modelo_130_full' => 'Modelo 130 - IRPF trimestral',
    'section_direct_estimation' => 'I. Actividades económicas en estimación directa',
    'row_01_income' => '[01] Ingresos computables del trimestre',
    'row_01_subtitle' => 'Facturas emitidas (base imponible)',
    'row_02_expenses' => '[02] Gastos fiscalmente deducibles',
    'row_02_subtitle' => 'Facturas recibidas + gastos',
    'row_03_net' => '[03] Rendimiento neto (01 - 02)',
    'row_04_pct' => '[04] :rate% de [03]',
    'row_05_withholdings' => '[05] Retenciones e ingresos a cuenta soportados',
    'row_05_subtitle' => 'IRPF retenido por clientes',
    'row_06_previous' => '[06] Pagos fraccionados de trimestres anteriores',
    'row_07_total' => '[07] Total a ingresar (04 - 05 - 06)',

    // Modelo 390
    'modelo_390' => 'Modelo 390',
    'modelo_390_full' => 'Modelo 390 - Resumen anual IVA',
    'year_label' => 'Ejercicio:',
    'annual_vat_charged' => 'IVA devengado - Resumen anual',
    'col_vat_base' => 'Base imponible',
    'col_vat_amount' => 'Cuota IVA',
    'annual_vat_deductible' => 'IVA deducible - Resumen anual',
    'quarterly_breakdown' => 'Desglose trimestral',
    'col_quarter' => 'Trimestre',
    'col_vat_charged' => 'IVA devengado',
    'col_vat_deductible_short' => 'IVA deducible',
    'col_difference' => 'Diferencia',
    'annual_result' => 'Resultado anual',

    // Modelo 303 enhanced
    'casilla_exempt' => '[13] Operaciones exentas',
    'casilla_surcharge' => 'Recargo de equivalencia',
    'col_surcharge' => 'Rec. equiv.',

    // Modelo 111
    'modelo_111' => 'Modelo 111',
    'modelo_111_full' => 'Modelo 111 - Retenciones profesionales',
    'section_professional' => 'Retenciones por actividades profesionales',
    'casilla_04_recipients' => '[04] Nº de perceptores',
    'casilla_05_base' => '[05] Base retenciones e ingresos a cuenta',
    'casilla_06_withheld' => '[06] Retenciones e ingresos a cuenta',
    'result_to_pay' => 'Resultado a ingresar',
    'no_retentions' => 'Sin retenciones en el periodo.',

    // Modelo 115
    'modelo_115' => 'Modelo 115',
    'modelo_115_full' => 'Modelo 115 - Retenciones alquileres',
    'section_rental' => 'Rentas o rendimientos procedentes del arrendamiento',
    'casilla_01_landlords' => '[01] Nº de arrendadores',
    'casilla_02_base' => '[02] Base retenciones e ingresos a cuenta',
    'casilla_03_withheld' => '[03] Retenciones e ingresos a cuenta',

    // Modelo 347
    'modelo_347' => 'Modelo 347',
    'modelo_347_full' => 'Modelo 347 - Operaciones con terceros',
    'section_sales' => 'A) Ventas y cobros',
    'section_purchases' => 'B) Compras y pagos',
    'col_province' => 'Provincia',
    'col_annual_total' => 'Total anual',
    'col_q1' => '1T',
    'col_q2' => '2T',
    'col_q3' => '3T',
    'col_q4' => '4T',
    'threshold_notice' => 'Se muestran operaciones que superan 3.005,06 € anuales por NIF.',
    'missing_nif_warning' => 'Algunos gastos sin NIF de proveedor fueron excluidos.',
    'no_operations_347' => 'Sin operaciones que superen el umbral.',
    'total_section' => 'Total sección',

    // PDF
    'generated_on' => 'Generado el',
    'pdf_period_label' => 'Periodo: :from a :to',
];
