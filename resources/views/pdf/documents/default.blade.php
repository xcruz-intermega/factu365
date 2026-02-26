<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: '{{ $settings['font_family'] ?? 'DejaVu Sans' }}', sans-serif;
            font-size: 9pt;
            color: #1f2937;
            line-height: 1.4;
        }
        .page { padding: 30px 40px; }

        /* Header */
        .header { margin-bottom: 25px; }
        .header table { width: 100%; }
        .header td { vertical-align: top; }
        .company-name { font-size: 16pt; font-weight: bold; color: {{ $settings['primary_color'] ?? '#1f2937' }}; }
        .company-info { font-size: 8pt; color: #6b7280; margin-top: 5px; line-height: 1.5; }
        .logo { max-width: 160px; max-height: 60px; }

        /* Document title */
        .doc-title {
            background-color: {{ $settings['accent_color'] ?? '#4f46e5' }};
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
            border-bottom: 2px solid {{ $settings['accent_color'] ?? '#4f46e5' }};
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
            border-top: 2px solid {{ $settings['accent_color'] ?? '#4f46e5' }};
            font-weight: bold;
            font-size: 11pt;
            padding-top: 8px;
            color: {{ $settings['accent_color'] ?? '#4f46e5' }};
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
    {{-- Header: Logo + Company Info --}}
    <div class="header">
        <table>
            <tr>
                <td style="width: 50%;">
                    @if(($settings['show_logo'] ?? true) && $company?->logo_path)
                        <img src="{{ storage_path('app/private/' . $company->logo_path) }}" class="logo" alt="Logo">
                        <br>
                    @endif
                    <span class="company-name">{{ $company?->trade_name ?: $company?->legal_name ?? '' }}</span>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div class="company-info">
                        @if($company)
                            <strong>{{ $company->legal_name }}</strong><br>
                            NIF: {{ $company->nif }}<br>
                            @if($company->address_street){{ $company->address_street }}<br>@endif
                            @if($company->address_postal_code || $company->address_city)
                                {{ $company->address_postal_code }} {{ $company->address_city }}
                                @if($company->address_province)({{ $company->address_province }})@endif
                                <br>
                            @endif
                            @if($company->phone)Tel: {{ $company->phone }}<br>@endif
                            @if($company->email){{ $company->email }}<br>@endif
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Document title bar --}}
    <div class="doc-title">
        {{ $typeLabel }}
        @if($document->number)
            <span class="doc-number">{{ $document->number }}</span>
        @endif
    </div>

    {{-- Client + Document info --}}
    <div class="info-row">
        <table>
            <tr>
                <td style="width: 55%; padding-right: 10px;">
                    <div class="info-box">
                        <div class="info-box-title">{{ __('pdf.client_data') }}</div>
                        @if($document->client)
                            <p class="name">{{ $document->client->trade_name ?: $document->client->legal_name }}</p>
                            @if($document->client->trade_name)
                                <p>{{ $document->client->legal_name }}</p>
                            @endif
                            <p>NIF: {{ $document->client->nif }}</p>
                            @if($document->client->address_street)
                                <p>{{ $document->client->address_street }}</p>
                            @endif
                            @if($document->client->address_postal_code || $document->client->address_city)
                                <p>{{ $document->client->address_postal_code }} {{ $document->client->address_city }}
                                @if($document->client->address_province) ({{ $document->client->address_province }})@endif
                                </p>
                            @endif
                        @else
                            <p class="name" style="color: #9ca3af;">{{ __('pdf.no_client') }}</p>
                        @endif
                    </div>
                </td>
                <td style="width: 45%;">
                    <div class="info-box">
                        <div class="info-box-title">{{ __('pdf.document_data') }}</div>
                        <p><strong>{{ __('pdf.issue_date') }}:</strong> {{ $document->issue_date?->format('d/m/Y') }}</p>
                        @if($document->due_date)
                            <p><strong>{{ __('pdf.due_date') }}:</strong> {{ $document->due_date->format('d/m/Y') }}</p>
                        @endif
                        @if($document->operation_date)
                            <p><strong>{{ __('pdf.operation_date') }}:</strong> {{ $document->operation_date->format('d/m/Y') }}</p>
                        @endif
                        @if($document->invoice_type)
                            <p><strong>{{ __('pdf.invoice_type') }}:</strong> {{ $document->invoice_type }}</p>
                        @endif
                        @if($document->correctedDocument)
                            <p><strong>{{ __('pdf.rectifies') }}:</strong> {{ $document->correctedDocument->number }}</p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Lines table --}}
    <table class="lines-table">
        <thead>
            <tr>
                <th style="width: 40%;">{{ __('pdf.concept') }}</th>
                <th class="text-right" style="width: 10%;">{{ __('pdf.quantity') }}</th>
                <th class="text-right" style="width: 13%;">{{ __('pdf.unit_price') }}</th>
                <th class="text-right" style="width: 10%;">{{ __('pdf.discount_short') }}</th>
                <th class="text-right" style="width: 10%;">{{ __('pdf.vat') }}</th>
                <th class="text-right" style="width: 17%;">{{ __('pdf.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr>
                    <td>
                        <div class="concept">{{ $line->concept }}</div>
                        @if($line->description)
                            <div class="description">{{ $line->description }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format((float)$line->quantity, 2, ',', '.') }}{{ $line->unit ? ' ' . $line->unit : '' }}</td>
                    <td class="text-right">{{ number_format((float)$line->unit_price, 2, ',', '.') }} €</td>
                    <td class="text-right">
                        @if((float)$line->discount_percent > 0)
                            {{ number_format((float)$line->discount_percent, 0) }}%
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">{{ number_format((float)$line->vat_rate, 0) }}%</td>
                    <td class="text-right">{{ number_format((float)$line->line_total, 2, ',', '.') }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- VAT Breakdown + Totals --}}
    <div class="totals-section clearfix">
        <table>
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    @if($vatBreakdown->count() > 0)
                        <table class="vat-table">
                            <thead>
                                <tr>
                                    <th>{{ __('pdf.vat_type') }}</th>
                                    <th class="text-right">{{ __('pdf.tax_base') }}</th>
                                    <th class="text-right">{{ __('pdf.vat_amount') }}</th>
                                    @if($vatBreakdown->sum('surcharge') > 0)
                                        <th class="text-right">{{ __('pdf.surcharge') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vatBreakdown as $vat)
                                    <tr>
                                        <td>{{ number_format($vat['vat_rate'], 0) }}%</td>
                                        <td class="text-right">{{ number_format($vat['base'], 2, ',', '.') }} €</td>
                                        <td class="text-right">{{ number_format($vat['vat'], 2, ',', '.') }} €</td>
                                        @if($vatBreakdown->sum('surcharge') > 0)
                                            <td class="text-right">{{ number_format($vat['surcharge'], 2, ',', '.') }} €</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
                <td style="width: 45%; vertical-align: top;">
                    <table class="totals-table">
                        <tr>
                            <td class="label">{{ __('pdf.subtotal') }}</td>
                            <td class="value">{{ number_format((float)$document->subtotal, 2, ',', '.') }} €</td>
                        </tr>
                        @if((float)$document->total_discount > 0)
                            <tr>
                                <td class="label">{{ __('pdf.discount') }}</td>
                                <td class="value">-{{ number_format((float)$document->total_discount, 2, ',', '.') }} €</td>
                            </tr>
                        @endif
                        <tr class="subtotal-row">
                            <td class="label">{{ __('pdf.tax_base') }}</td>
                            <td class="value">{{ number_format((float)$document->tax_base, 2, ',', '.') }} €</td>
                        </tr>
                        <tr>
                            <td class="label">{{ __('pdf.vat') }}</td>
                            <td class="value">{{ number_format((float)$document->total_vat, 2, ',', '.') }} €</td>
                        </tr>
                        @if((float)$document->total_surcharge > 0)
                            <tr>
                                <td class="label">{{ __('pdf.equivalence_surcharge') }}</td>
                                <td class="value">{{ number_format((float)$document->total_surcharge, 2, ',', '.') }} €</td>
                            </tr>
                        @endif
                        @if((float)$document->total_irpf > 0)
                            <tr>
                                <td class="label">{{ __('pdf.irpf_withholding') }}</td>
                                <td class="value" style="color: #dc2626;">-{{ number_format((float)$document->total_irpf, 2, ',', '.') }} €</td>
                            </tr>
                        @endif
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td class="value">{{ number_format((float)$document->total, 2, ',', '.') }} €</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Notes --}}
    @if($document->notes)
        <div class="notes">
            <div class="notes-title">{{ __('pdf.notes') }}</div>
            {{ $document->notes }}
        </div>
    @endif

    {{-- Footer text --}}
    @if($document->footer_text)
        <div class="notes">
            {{ $document->footer_text }}
        </div>
    @endif

    {{-- QR Code (VeriFactu) --}}
    @if(($settings['show_qr'] ?? true) && $qrDataUri)
        <div class="qr-section">
            <table>
                <tr>
                    <td style="width: 90px;">
                        <img src="{{ $qrDataUri }}" class="qr-img" alt="QR VeriFactu">
                    </td>
                    <td>
                        <div class="qr-text">
                            <strong>{{ __('pdf.qr_title') }}</strong><br>
                            {{ __('pdf.qr_description') }}<br>
                            {{ __('pdf.qr_scan') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    {{-- Legal text --}}
    <div class="legal-text">
        @if(in_array($document->document_type, ['invoice', 'rectificative']))
            {{ __('pdf.legal_verifactu') }}
            @if($company?->software_name)
                Software: {{ $company->software_name }} v{{ $company->software_version ?? '1.0' }}.
            @endif
        @endif
    </div>

    {{-- Page footer --}}
    <div class="page-footer">
        {{ $company?->legal_name ?? '' }} &middot; NIF: {{ $company?->nif ?? '' }}
        @if($company?->website) &middot; {{ $company->website }}@endif
    </div>
</div>
</body>
</html>
