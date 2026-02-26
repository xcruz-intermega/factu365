<?php

return [
    // Sales by client
    'sales_by_client' => 'Vendes per client',
    'col_client' => 'Client',
    'col_nif' => 'NIF',
    'col_invoices' => 'Factures',
    'no_data' => 'Sense dades per al període seleccionat.',

    // Sales by product
    'sales_by_product' => 'Vendes per producte',
    'sales_by_product_full' => 'Vendes per producte/servei',
    'col_product_concept' => 'Producte/Concepte',
    'col_quantity' => 'Quantitat',

    // Sales by period
    'sales_by_period' => 'Vendes per període',
    'group_by' => 'Agrupar per',
    'group_month' => 'Mes',
    'group_quarter' => 'Trimestre',
    'chart_billing' => 'Facturació',
    'col_period' => 'Període',

    // Modelo 303
    'modelo_303' => 'Model 303',
    'modelo_303_full' => 'Model 303 - IVA trimestral',
    'fiscal_year' => 'Exercici',
    'quarter' => 'Trimestre',
    'q1_months' => 'Gener - Març',
    'q2_months' => 'Abril - Juny',
    'q3_months' => 'Juliol - Setembre',
    'q4_months' => 'Octubre - Desembre',
    'declarant' => 'Declarant:',
    'period' => 'Període:',
    'vat_charged' => 'IVA meritat (repercutit)',
    'issued_invoices' => 'Factures emeses',
    'vat_type' => 'Tipus IVA',
    'col_base' => 'Base',
    'col_amount' => 'Quota',
    'no_operations' => 'Sense operacions',
    'total_vat_charged' => 'Total IVA meritat',
    'vat_deductible' => 'IVA deduïble (suportat)',
    'received_invoices' => 'Factures rebudes i despeses',
    'total_vat_deductible' => 'Total IVA deduïble',
    'settlement_result' => 'Resultat liquidació',
    'difference' => 'Diferència (a ingressar / a tornar)',
    'fiscal_disclaimer' => '* Aquest és un esborrany orientatiu. Reviseu les dades amb el vostre assessor fiscal abans de presentar el model oficial.',

    // Modelo 130
    'modelo_130' => 'Model 130',
    'modelo_130_full' => 'Model 130 - IRPF trimestral',
    'section_direct_estimation' => 'I. Activitats econòmiques en estimació directa',
    'row_01_income' => '[01] Ingressos computables del trimestre',
    'row_01_subtitle' => 'Factures emeses (base imposable)',
    'row_02_expenses' => '[02] Despeses fiscalment deduïbles',
    'row_02_subtitle' => 'Factures rebudes + despeses',
    'row_03_net' => '[03] Rendiment net (01 - 02)',
    'row_04_pct' => '[04] :rate% de [03]',
    'row_05_withholdings' => '[05] Retencions i ingressos a compte suportats',
    'row_05_subtitle' => 'IRPF retingut per clients',
    'row_06_previous' => '[06] Pagaments fraccionats de trimestres anteriors',
    'row_07_total' => '[07] Total a ingressar (04 - 05 - 06)',

    // Modelo 390
    'modelo_390' => 'Model 390',
    'modelo_390_full' => 'Model 390 - Resum anual IVA',
    'year_label' => 'Exercici:',
    'annual_vat_charged' => 'IVA meritat - Resum anual',
    'col_vat_base' => 'Base imposable',
    'col_vat_amount' => 'Quota IVA',
    'annual_vat_deductible' => 'IVA deduïble - Resum anual',
    'quarterly_breakdown' => 'Desglossament trimestral',
    'col_quarter' => 'Trimestre',
    'col_vat_charged' => 'IVA meritat',
    'col_vat_deductible_short' => 'IVA deduïble',
    'col_difference' => 'Diferència',
    'annual_result' => 'Resultat anual',

    // Modelo 303 enhanced
    'casilla_exempt' => '[13] Operacions exemptes',
    'casilla_surcharge' => 'Recàrrec d\'equivalència',
    'col_surcharge' => 'Rec. equiv.',

    // Modelo 111
    'modelo_111' => 'Model 111',
    'modelo_111_full' => 'Model 111 - Retencions professionals',
    'section_professional' => 'Retencions per activitats professionals',
    'casilla_04_recipients' => '[04] Nre. de perceptors',
    'casilla_05_base' => '[05] Base retencions i ingressos a compte',
    'casilla_06_withheld' => '[06] Retencions i ingressos a compte',
    'result_to_pay' => 'Resultat a ingressar',
    'no_retentions' => 'Sense retencions en el període.',

    // Modelo 115
    'modelo_115' => 'Model 115',
    'modelo_115_full' => 'Model 115 - Retencions lloguers',
    'section_rental' => 'Rendes procedents de l\'arrendament',
    'casilla_01_landlords' => '[01] Nre. d\'arrendadors',
    'casilla_02_base' => '[02] Base retencions i ingressos a compte',
    'casilla_03_withheld' => '[03] Retencions i ingressos a compte',

    // Modelo 347
    'modelo_347' => 'Model 347',
    'modelo_347_full' => 'Model 347 - Operacions amb tercers',
    'section_sales' => 'A) Vendes i cobraments',
    'section_purchases' => 'B) Compres i pagaments',
    'col_province' => 'Província',
    'col_annual_total' => 'Total anual',
    'col_q1' => '1T',
    'col_q2' => '2T',
    'col_q3' => '3T',
    'col_q4' => '4T',
    'threshold_notice' => 'Es mostren operacions que superen 3.005,06 € anuals per NIF.',
    'missing_nif_warning' => 'Algunes despeses sense NIF de proveïdor s\'han exclòs.',
    'no_operations_347' => 'Sense operacions que superin el llindar.',
    'total_section' => 'Total secció',

    // PDF
    'generated_on' => 'Generat el',
    'pdf_period_label' => 'Període: :from a :to',
];
