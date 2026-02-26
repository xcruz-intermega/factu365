<?php

return [
    // Sales by client
    'sales_by_client' => 'Sales by client',
    'col_client' => 'Client',
    'col_nif' => 'Tax ID',
    'col_invoices' => 'Invoices',
    'no_data' => 'No data for the selected period.',

    // Sales by product
    'sales_by_product' => 'Sales by product',
    'sales_by_product_full' => 'Sales by product/service',
    'col_product_concept' => 'Product/Description',
    'col_quantity' => 'Quantity',

    // Sales by period
    'sales_by_period' => 'Sales by period',
    'group_by' => 'Group by',
    'group_month' => 'Month',
    'group_quarter' => 'Quarter',
    'chart_billing' => 'Billing',
    'col_period' => 'Period',

    // Modelo 303
    'modelo_303' => 'Modelo 303',
    'modelo_303_full' => 'Modelo 303 - Quarterly VAT',
    'fiscal_year' => 'Fiscal year',
    'quarter' => 'Quarter',
    'q1_months' => 'January - March',
    'q2_months' => 'April - June',
    'q3_months' => 'July - September',
    'q4_months' => 'October - December',
    'declarant' => 'Declarant:',
    'period' => 'Period:',
    'vat_charged' => 'VAT charged (output VAT)',
    'issued_invoices' => 'Issued invoices',
    'vat_type' => 'VAT rate',
    'col_base' => 'Tax base',
    'col_amount' => 'VAT amount',
    'no_operations' => 'No operations',
    'total_vat_charged' => 'Total VAT charged',
    'vat_deductible' => 'VAT deductible (input VAT)',
    'received_invoices' => 'Purchase invoices and expenses',
    'total_vat_deductible' => 'Total VAT deductible',
    'settlement_result' => 'Settlement result',
    'difference' => 'Difference (payable / refundable)',
    'fiscal_disclaimer' => '* This is an indicative draft. Review the data with your tax adviser before submitting the official return.',

    // Modelo 130
    'modelo_130' => 'Modelo 130',
    'modelo_130_full' => 'Modelo 130 - Quarterly IRPF',
    'section_direct_estimation' => 'I. Economic activities under direct estimation',
    'row_01_income' => '[01] Reckonable income for the quarter',
    'row_01_subtitle' => 'Issued invoices (tax base)',
    'row_02_expenses' => '[02] Tax-deductible expenses',
    'row_02_subtitle' => 'Purchase invoices + expenses',
    'row_03_net' => '[03] Net income (01 - 02)',
    'row_04_pct' => '[04] :rate% of [03]',
    'row_05_withholdings' => '[05] Withholdings and payments on account',
    'row_05_subtitle' => 'IRPF withheld by clients',
    'row_06_previous' => '[06] Instalments from previous quarters',
    'row_07_total' => '[07] Total payable (04 - 05 - 06)',

    // Modelo 390
    'modelo_390' => 'Modelo 390',
    'modelo_390_full' => 'Modelo 390 - Annual VAT summary',
    'year_label' => 'Fiscal year:',
    'annual_vat_charged' => 'VAT charged - Annual summary',
    'col_vat_base' => 'Tax base',
    'col_vat_amount' => 'VAT amount',
    'annual_vat_deductible' => 'VAT deductible - Annual summary',
    'quarterly_breakdown' => 'Quarterly breakdown',
    'col_quarter' => 'Quarter',
    'col_vat_charged' => 'VAT charged',
    'col_vat_deductible_short' => 'VAT deductible',
    'col_difference' => 'Difference',
    'annual_result' => 'Annual result',

    // Modelo 303 enhanced
    'casilla_exempt' => '[13] Exempt operations',
    'casilla_surcharge' => 'Equivalence surcharge',
    'col_surcharge' => 'Surcharge',

    // Modelo 111
    'modelo_111' => 'Modelo 111',
    'modelo_111_full' => 'Modelo 111 - Professional withholdings',
    'section_professional' => 'Withholdings for professional activities',
    'casilla_04_recipients' => '[04] No. of recipients',
    'casilla_05_base' => '[05] Withholding base',
    'casilla_06_withheld' => '[06] Withholdings and payments on account',
    'result_to_pay' => 'Amount payable',
    'no_retentions' => 'No withholdings in the period.',

    // Modelo 115
    'modelo_115' => 'Modelo 115',
    'modelo_115_full' => 'Modelo 115 - Rental withholdings',
    'section_rental' => 'Income from leasing/rental',
    'casilla_01_landlords' => '[01] No. of landlords',
    'casilla_02_base' => '[02] Withholding base',
    'casilla_03_withheld' => '[03] Withholdings and payments on account',

    // Modelo 347
    'modelo_347' => 'Modelo 347',
    'modelo_347_full' => 'Modelo 347 - Third-party operations',
    'section_sales' => 'A) Sales and receipts',
    'section_purchases' => 'B) Purchases and payments',
    'col_province' => 'Province',
    'col_annual_total' => 'Annual total',
    'col_q1' => 'Q1',
    'col_q2' => 'Q2',
    'col_q3' => 'Q3',
    'col_q4' => 'Q4',
    'threshold_notice' => 'Showing operations exceeding €3,005.06 per year per Tax ID.',
    'missing_nif_warning' => 'Some expenses without supplier Tax ID were excluded.',
    'no_operations_347' => 'No operations exceeding the threshold.',
    'total_section' => 'Section total',

    // PDF
    'generated_on' => 'Generated on',
    'pdf_period_label' => 'Period: :from to :to',
];
