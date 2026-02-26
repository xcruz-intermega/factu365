<?php

return [
    // Section headers
    'client_data' => 'Client details',
    'document_data' => 'Document details',
    'issuer' => 'Issuer',
    'client' => 'Client',
    'no_client' => 'No client assigned',
    'summary' => 'Summary',
    'conditions' => 'Terms',
    'notes' => 'Notes',
    'draft' => 'DRAFT',

    // Dates
    'issue_date' => 'Issue date',
    'due_date' => 'Due date',
    'operation_date' => 'Operation date',
    'invoice_type' => 'Invoice type',
    'rectifies' => 'Corrects',
    'rectificative_of' => 'Corrective invoice for:',

    // Lines table headers
    'concept' => 'Description',
    'quantity' => 'Quantity',
    'qty_short' => 'Qty',
    'unit_price' => 'Unit price',
    'price' => 'Price',
    'discount_short' => 'Disc.',
    'vat' => 'VAT',
    'amount' => 'Amount',
    'unit' => 'Unit',

    // VAT breakdown
    'vat_type' => 'VAT rate',
    'vat_type_short' => 'Rate',
    'tax_base' => 'Taxable base',
    'tax_base_short' => 'Base',
    'vat_amount' => 'VAT amount',
    'vat_amount_short' => 'VAT',
    'surcharge' => 'Surcharge',
    'surcharge_short' => 'Surcharge',

    // Totals
    'subtotal' => 'Subtotal',
    'discount' => 'Discount',
    'equivalence_surcharge' => 'Equivalence surcharge',
    'equivalence_surcharge_short' => 'Equiv. surch.',
    'irpf_withholding' => 'IRPF withholding',
    'total' => 'TOTAL',

    // Template names
    'documents' => [
        'default' => 'Classic',
        'modern'  => 'Modern',
    ],

    // QR VeriFactu (legal text stays in Spanish per regulation)
    'qr_title' => 'Código QR VERI*FACTU',
    'qr_description' => 'Factura verificable conforme al RD 1007/2023.',
    'qr_scan' => 'Escanee el código QR para verificar la autenticidad de esta factura en la Agencia Tributaria.',
    'qr_scan_short' => 'Escanee el código QR para verificar esta factura en la Agencia Tributaria.',

    // Legal text (stays in Spanish per VeriFactu regulation)
    'legal_verifactu' => 'Factura generada por sistema informático conforme al Reglamento VERI*FACTU (RD 1007/2023).',
];
