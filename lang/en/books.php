<?php

return [
    // Titles
    'libro_ventas' => 'Sales Ledger',
    'libro_compras' => 'Purchases Ledger',
    'libro_expedidas' => 'Issued Invoices Register',
    'libro_recibidas' => 'Received Invoices Register',

    // Columns common
    'col_date' => 'Date',
    'col_number' => 'Number',
    'col_series' => 'Series',
    'col_client' => 'Client',
    'col_supplier' => 'Supplier',
    'col_nif' => 'Tax ID',
    'col_base' => 'Tax base',
    'col_vat' => 'VAT',
    'col_irpf' => 'IRPF',
    'col_total' => 'Total',
    'col_invoice_number' => 'Invoice no.',
    'col_origin' => 'Source',

    // Libro Expedidas (AEAT format)
    'col_invoice_type' => 'Inv. type',
    'col_regime' => 'Regime',
    'col_surcharge' => 'Surcharge',
    'col_name' => 'Name/Legal name',

    // Origins
    'origin_document' => 'Document',
    'origin_expense' => 'Expense',

    // Monthly subtotals
    'monthly_subtotal' => ':month subtotal',

    // Libro Recibidas (AEAT format)
    'col_reception_number' => 'Recep. no.',
    'col_operation_date' => 'Operation date',
    'col_regime_key' => 'Regime key',

    // Invoice types (AEAT)
    'invoice_type_F1' => 'F1 - Invoice',
    'invoice_type_F2' => 'F2 - Simplified invoice',
    'invoice_type_F3' => 'F3 - Invoice replacing simplified',
    'invoice_type_R1' => 'R1 - Credit note',
    'invoice_type_R2' => 'R2 - Credit note (art. 80.3)',
    'invoice_type_R3' => 'R3 - Credit note (art. 80.4)',
    'invoice_type_R4' => 'R4 - Credit note (other)',
    'invoice_type_R5' => 'R5 - Simplified credit note',

    // Regime keys (AEAT)
    'regime_01' => '01 - General regime',
    'regime_02' => '02 - Exports',
    'regime_03' => '03 - Used goods',
    'regime_05' => '05 - Travel agencies special regime',
    'regime_07' => '07 - Cash accounting special regime',

    // AEAT export
    'export_aeat_csv' => 'AEAT CSV',
    'aeat_csv_tooltip' => 'Official AEAT format (1 row per VAT type)',

    // Totals
    'total' => 'Total',
    'total_period' => 'Period total',

    // No data
    'no_data' => 'No data for the selected period.',

    // VAT breakdown
    'vat_breakdown' => 'VAT breakdown',
    'vat_rate' => 'Rate',
    'vat_base' => 'Base',
    'vat_amount' => 'Amount',

    // PDF
    'pdf_period_label' => 'Period: :from to :to',
];
