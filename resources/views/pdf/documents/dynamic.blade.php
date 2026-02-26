<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: '{{ $global['font_family'] ?? 'DejaVu Sans' }}', sans-serif;
            font-size: {{ $global['font_size'] ?? '9pt' }};
            color: #1f2937;
            line-height: 1.4;
        }
        .page { padding: 30px 40px; }

        /* Header */
        .header { margin-bottom: 25px; }
        .header table { width: 100%; }
        .header td { vertical-align: top; }
        .company-name { font-size: 16pt; font-weight: bold; color: {{ $global['primary_color'] ?? '#1f2937' }}; }
        .company-info { font-size: 8pt; color: #6b7280; margin-top: 5px; line-height: 1.5; }
        .logo { max-width: 160px; max-height: 60px; }

        /* Document title */
        .doc-title {
            background-color: {{ $global['accent_color'] ?? '#4f46e5' }};
            color: #ffffff;
            padding: 10px 15px;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .doc-number { float: right; font-size: 12pt; }

        /* Info boxes */
        .info-row { margin-bottom: 20px; }
        .info-row table { width: 100%; }
        .info-row td { vertical-align: top; }
        .info-box { border: 1px solid #e5e7eb; padding: 10px; border-radius: 4px; }
        .info-box-title { font-size: 7pt; text-transform: uppercase; color: #9ca3af; font-weight: bold; margin-bottom: 5px; letter-spacing: 0.5px; }
        .info-box p { margin-bottom: 2px; }
        .info-box .name { font-weight: bold; font-size: 10pt; }

        /* Lines table */
        .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .lines-table th {
            background-color: #f3f4f6;
            border-bottom: 2px solid {{ $global['accent_color'] ?? '#4f46e5' }};
            padding: 6px 8px;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
        }
        .lines-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 8.5pt;
        }
        .lines-table .text-right { text-align: right; }
        .lines-table .concept { font-weight: 600; }
        .lines-table .description { color: #6b7280; font-size: 7.5pt; }

        /* Totals */
        .totals-section { margin-bottom: 20px; }
        .totals-section table { width: 100%; }
        .totals-box { width: 280px; float: right; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 4px 8px; font-size: 8.5pt; }
        .totals-table .label { color: #6b7280; }
        .totals-table .value { text-align: right; }
        .totals-table .total-row td {
            border-top: 2px solid {{ $global['accent_color'] ?? '#4f46e5' }};
            font-weight: bold;
            font-size: 11pt;
            padding-top: 8px;
            color: {{ $global['accent_color'] ?? '#4f46e5' }};
        }
        .totals-table .subtotal-row td { border-top: 1px solid #e5e7eb; }

        /* VAT breakdown */
        .vat-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .vat-table th { background-color: #f9fafb; padding: 4px 8px; text-align: left; font-size: 7pt; text-transform: uppercase; color: #9ca3af; border-bottom: 1px solid #e5e7eb; }
        .vat-table td { padding: 4px 8px; font-size: 8pt; border-bottom: 1px solid #f3f4f6; }
        .vat-table .text-right { text-align: right; }

        /* Footer area */
        .footer-area { margin-top: 20px; }
        .notes { background-color: #f9fafb; padding: 10px; border-radius: 4px; font-size: 8pt; color: #6b7280; margin-bottom: 15px; }
        .notes-title { font-weight: bold; color: #374151; margin-bottom: 3px; }
        .legal-text { font-size: 7pt; color: #9ca3af; margin-top: 10px; line-height: 1.4; }

        /* QR section */
        .qr-section { margin-top: 15px; }
        .qr-section table { width: 100%; }
        .qr-section td { vertical-align: middle; }
        .qr-img { width: 80px; height: 80px; }
        .qr-text { font-size: 7pt; color: #9ca3af; padding-left: 10px; }

        /* Page footer */
        .page-footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            font-size: 7pt;
            color: #d1d5db;
            text-align: center;
            border-top: 1px solid #f3f4f6;
            padding-top: 5px;
        }

        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>
<div class="page">
    @foreach($blocks as $block)
        @include("pdf.documents.blocks.{$block['type']}", [
            'options' => $block['options'] ?? [],
            'global' => $global,
            'document' => $document,
            'company' => $company,
            'lines' => $lines,
            'vatBreakdown' => $vatBreakdown,
            'qrDataUri' => $qrDataUri ?? null,
            'typeLabel' => $typeLabel,
            'settings' => $settings ?? [],
        ])
    @endforeach
</div>
</body>
</html>
