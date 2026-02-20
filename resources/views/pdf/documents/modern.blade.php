<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @php
        $primary = $settings['primary_color'] ?? '#4f46e5';
        $accent = $settings['accent_color'] ?? '#6366f1';
        $fontFamily = $settings['font_family'] ?? 'DejaVu Sans';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: '{{ $fontFamily }}', sans-serif;
            font-size: 9pt;
            color: #374151;
            line-height: 1.4;
        }
        .page { padding: 0; }

        /* Colored header band */
        .header-band {
            background-color: {{ $primary }};
            color: #ffffff;
            padding: 25px 40px;
        }
        .header-band table { width: 100%; }
        .header-band td { vertical-align: middle; }
        .brand-name { font-size: 18pt; font-weight: bold; }
        .brand-sub { font-size: 8pt; opacity: 0.8; margin-top: 3px; }
        .doc-badge {
            text-align: right;
        }
        .doc-badge .type-label {
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }
        .doc-badge .number {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 2px;
        }
        .logo-modern { max-width: 140px; max-height: 50px; }

        /* Content */
        .content { padding: 25px 40px; }

        /* Info cards */
        .info-cards { margin-bottom: 20px; }
        .info-cards table { width: 100%; }
        .info-cards td { vertical-align: top; padding: 0; }
        .info-card {
            background-color: #f9fafb;
            border-left: 3px solid {{ $accent }};
            padding: 12px 15px;
            margin-bottom: 0;
        }
        .info-card-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: {{ $primary }};
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .info-card p { margin-bottom: 2px; font-size: 8.5pt; }
        .info-card .highlight { font-weight: bold; font-size: 10pt; color: #111827; }

        /* Date strip */
        .date-strip {
            background-color: {{ $primary }}10;
            border: 1px solid {{ $primary }}30;
            border-radius: 4px;
            padding: 8px 15px;
            margin-bottom: 20px;
        }
        .date-strip table { width: 100%; }
        .date-strip td { font-size: 8.5pt; }
        .date-strip .label { color: #6b7280; font-size: 7pt; text-transform: uppercase; }
        .date-strip .value { font-weight: bold; color: {{ $primary }}; }

        /* Lines table */
        .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .lines-table th {
            background-color: {{ $primary }};
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .lines-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 8.5pt;
        }
        .lines-table tbody tr:nth-child(even) { background-color: #fafafa; }
        .lines-table .text-right { text-align: right; }
        .lines-table .concept { font-weight: 600; color: #111827; }
        .lines-table .description { color: #9ca3af; font-size: 7.5pt; }

        /* Totals */
        .totals-area { margin-bottom: 20px; }
        .totals-area table { width: 100%; }
        .totals-area td { vertical-align: top; }

        .vat-breakdown { width: 100%; border-collapse: collapse; }
        .vat-breakdown th { background-color: #f3f4f6; padding: 5px 8px; text-align: left; font-size: 7pt; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .vat-breakdown td { padding: 5px 8px; font-size: 8pt; border-bottom: 1px solid #f3f4f6; }
        .vat-breakdown .text-right { text-align: right; }

        .totals-box {
            border: 2px solid {{ $primary }};
            border-radius: 6px;
            overflow: hidden;
        }
        .totals-box-header {
            background-color: {{ $primary }};
            color: #ffffff;
            padding: 6px 12px;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }
        .totals-box-body { padding: 8px 12px; }
        .totals-row { padding: 3px 0; font-size: 8.5pt; }
        .totals-row table { width: 100%; }
        .totals-row .label { color: #6b7280; }
        .totals-row .value { text-align: right; }
        .totals-grand {
            border-top: 2px solid {{ $primary }};
            padding-top: 8px;
            margin-top: 5px;
        }
        .totals-grand table { width: 100%; }
        .totals-grand .label { font-size: 11pt; font-weight: bold; color: {{ $primary }}; }
        .totals-grand .value { text-align: right; font-size: 13pt; font-weight: bold; color: {{ $primary }}; }

        /* Notes */
        .notes-section {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 15px;
            font-size: 8pt;
        }
        .notes-title { font-weight: bold; color: #92400e; margin-bottom: 3px; font-size: 7pt; text-transform: uppercase; }

        /* QR section */
        .qr-section {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-top: 15px;
        }
        .qr-section table { width: 100%; }
        .qr-section td { vertical-align: middle; }
        .qr-img { width: 75px; height: 75px; }
        .qr-label { font-size: 8pt; font-weight: bold; color: {{ $primary }}; margin-bottom: 3px; }
        .qr-text { font-size: 7pt; color: #6b7280; line-height: 1.4; }

        /* Legal & footer */
        .legal-text { font-size: 7pt; color: #d1d5db; margin-top: 15px; }
        .page-footer {
            position: fixed;
            bottom: 15px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 7pt;
            color: #d1d5db;
            border-top: 1px solid #f3f4f6;
            padding-top: 5px;
        }
    </style>
</head>
<body>
<div class="page">
    {{-- Colored header --}}
    <div class="header-band">
        <table>
            <tr>
                <td style="width: 55%;">
                    @if(($settings['show_logo'] ?? true) && $company?->logo_path)
                        <img src="{{ storage_path('app/private/' . $company->logo_path) }}" class="logo-modern" alt="Logo"><br>
                    @endif
                    <div class="brand-name">{{ $company?->trade_name ?: $company?->legal_name ?? 'Mi Empresa' }}</div>
                    <div class="brand-sub">{{ $company?->nif ?? '' }}</div>
                </td>
                <td style="width: 45%;">
                    <div class="doc-badge">
                        <div class="type-label">{{ $typeLabel }}</div>
                        <div class="number">{{ $document->number ?: 'BORRADOR' }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        {{-- Date strip --}}
        <div class="date-strip">
            <table>
                <tr>
                    <td style="width: 33%;">
                        <span class="label">Fecha emisión</span><br>
                        <span class="value">{{ $document->issue_date?->format('d/m/Y') }}</span>
                    </td>
                    @if($document->due_date)
                        <td style="width: 33%;">
                            <span class="label">Fecha vencimiento</span><br>
                            <span class="value">{{ $document->due_date->format('d/m/Y') }}</span>
                        </td>
                    @endif
                    @if($document->operation_date)
                        <td style="width: 33%;">
                            <span class="label">Fecha operación</span><br>
                            <span class="value">{{ $document->operation_date->format('d/m/Y') }}</span>
                        </td>
                    @endif
                </tr>
            </table>
        </div>

        {{-- Info cards: Company + Client --}}
        <div class="info-cards">
            <table>
                <tr>
                    <td style="width: 48%; padding-right: 8px;">
                        <div class="info-card">
                            <div class="info-card-label">Emisor</div>
                            @if($company)
                                <p class="highlight">{{ $company->legal_name }}</p>
                                <p>NIF: {{ $company->nif }}</p>
                                @if($company->address_street)<p>{{ $company->address_street }}</p>@endif
                                @if($company->address_postal_code || $company->address_city)
                                    <p>{{ $company->address_postal_code }} {{ $company->address_city }}</p>
                                @endif
                                @if($company->email)<p>{{ $company->email }}</p>@endif
                            @endif
                        </div>
                    </td>
                    <td style="width: 4%;"></td>
                    <td style="width: 48%;">
                        <div class="info-card">
                            <div class="info-card-label">Cliente</div>
                            @if($document->client)
                                <p class="highlight">{{ $document->client->trade_name ?: $document->client->legal_name }}</p>
                                @if($document->client->trade_name)<p>{{ $document->client->legal_name }}</p>@endif
                                <p>NIF: {{ $document->client->nif }}</p>
                                @if($document->client->address_street)<p>{{ $document->client->address_street }}</p>@endif
                                @if($document->client->address_postal_code || $document->client->address_city)
                                    <p>{{ $document->client->address_postal_code }} {{ $document->client->address_city }}</p>
                                @endif
                            @else
                                <p class="highlight" style="color: #9ca3af;">Sin cliente asignado</p>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if($document->correctedDocument)
            <div class="date-strip" style="background-color: #fef2f2; border-color: #fecaca;">
                <span class="label" style="color: #991b1b;">Factura rectificativa de:</span>
                <span class="value" style="color: #dc2626;">{{ $document->correctedDocument->number }}</span>
            </div>
        @endif

        {{-- Lines table --}}
        <table class="lines-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Concepto</th>
                    <th class="text-right" style="width: 10%;">Cant.</th>
                    <th class="text-right" style="width: 13%;">Precio</th>
                    <th class="text-right" style="width: 10%;">Dto.</th>
                    <th class="text-right" style="width: 10%;">IVA</th>
                    <th class="text-right" style="width: 17%;">Importe</th>
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

        {{-- Totals area --}}
        <div class="totals-area">
            <table>
                <tr>
                    <td style="width: 52%; padding-right: 15px; vertical-align: top;">
                        @if($vatBreakdown->count() > 0)
                            <table class="vat-breakdown">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th class="text-right">Base</th>
                                        <th class="text-right">Cuota</th>
                                        @if($vatBreakdown->sum('surcharge') > 0)
                                            <th class="text-right">Rec.</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vatBreakdown as $vat)
                                        <tr>
                                            <td>IVA {{ number_format($vat['vat_rate'], 0) }}%</td>
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
                    <td style="width: 48%; vertical-align: top;">
                        <div class="totals-box">
                            <div class="totals-box-header">Resumen</div>
                            <div class="totals-box-body">
                                <div class="totals-row">
                                    <table><tr>
                                        <td class="label">Subtotal</td>
                                        <td class="value">{{ number_format((float)$document->subtotal, 2, ',', '.') }} €</td>
                                    </tr></table>
                                </div>
                                @if((float)$document->total_discount > 0)
                                    <div class="totals-row">
                                        <table><tr>
                                            <td class="label">Descuento</td>
                                            <td class="value">-{{ number_format((float)$document->total_discount, 2, ',', '.') }} €</td>
                                        </tr></table>
                                    </div>
                                @endif
                                <div class="totals-row">
                                    <table><tr>
                                        <td class="label">Base imponible</td>
                                        <td class="value">{{ number_format((float)$document->tax_base, 2, ',', '.') }} €</td>
                                    </tr></table>
                                </div>
                                <div class="totals-row">
                                    <table><tr>
                                        <td class="label">IVA</td>
                                        <td class="value">{{ number_format((float)$document->total_vat, 2, ',', '.') }} €</td>
                                    </tr></table>
                                </div>
                                @if((float)$document->total_surcharge > 0)
                                    <div class="totals-row">
                                        <table><tr>
                                            <td class="label">Recargo eq.</td>
                                            <td class="value">{{ number_format((float)$document->total_surcharge, 2, ',', '.') }} €</td>
                                        </tr></table>
                                    </div>
                                @endif
                                @if((float)$document->total_irpf > 0)
                                    <div class="totals-row">
                                        <table><tr>
                                            <td class="label">Retención IRPF</td>
                                            <td class="value" style="color: #dc2626;">-{{ number_format((float)$document->total_irpf, 2, ',', '.') }} €</td>
                                        </tr></table>
                                    </div>
                                @endif
                                <div class="totals-grand">
                                    <table><tr>
                                        <td class="label">TOTAL</td>
                                        <td class="value">{{ number_format((float)$document->total, 2, ',', '.') }} €</td>
                                    </tr></table>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Notes --}}
        @if($document->notes)
            <div class="notes-section">
                <div class="notes-title">Observaciones</div>
                {{ $document->notes }}
            </div>
        @endif

        @if($document->footer_text)
            <div class="notes-section" style="background-color: #f9fafb; border-color: #e5e7eb;">
                <div class="notes-title" style="color: #374151;">Condiciones</div>
                {{ $document->footer_text }}
            </div>
        @endif

        {{-- QR Code --}}
        @if(($settings['show_qr'] ?? true) && $qrDataUri)
            <div class="qr-section">
                <table>
                    <tr>
                        <td style="width: 85px;">
                            <img src="{{ $qrDataUri }}" class="qr-img" alt="QR VeriFactu">
                        </td>
                        <td style="padding-left: 10px;">
                            <div class="qr-label">VERI*FACTU</div>
                            <div class="qr-text">
                                Factura verificable conforme al RD 1007/2023.<br>
                                Escanee el código QR para verificar esta factura en la Agencia Tributaria.
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        {{-- Legal text --}}
        <div class="legal-text">
            @if(in_array($document->document_type, ['invoice', 'rectificative']))
                Factura generada por sistema informático conforme al Reglamento VERI*FACTU (RD 1007/2023).
                @if($company?->software_name)
                    Software: {{ $company->software_name }} v{{ $company->software_version ?? '1.0' }}.
                @endif
            @endif
        </div>
    </div>

    {{-- Page footer --}}
    <div class="page-footer">
        {{ $company?->legal_name ?? '' }} &middot; NIF: {{ $company?->nif ?? '' }}
        @if($company?->phone) &middot; {{ $company->phone }}@endif
        @if($company?->website) &middot; {{ $company->website }}@endif
    </div>
</div>
</body>
</html>
