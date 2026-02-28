<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1f2937;
            line-height: 1.4;
        }
        .page { padding: 30px 40px; }

        /* Header */
        .header { margin-bottom: 20px; }
        .header table { width: 100%; }
        .header td { vertical-align: top; }
        .company-name { font-size: 14pt; font-weight: bold; color: #1e40af; }
        .company-info { font-size: 7.5pt; color: #6b7280; margin-top: 4px; line-height: 1.5; }

        /* Report title */
        .report-title {
            background-color: #2563eb;
            color: #ffffff;
            padding: 8px 15px;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-subtitle {
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 15px;
        }

        /* Tables */
        .report-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .report-table th {
            background-color: #f3f4f6;
            border-bottom: 2px solid #2563eb;
            padding: 5px 8px;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
        }
        .report-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 8.5pt;
        }
        .report-table .text-right { text-align: right; }
        .report-table tfoot td {
            background-color: #f3f4f6;
            font-weight: bold;
            border-top: 2px solid #2563eb;
            padding: 6px 8px;
        }

        /* Section headers */
        .section-header {
            background-color: #eff6ff;
            border-left: 3px solid #2563eb;
            padding: 6px 10px;
            font-size: 9pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            margin-top: 15px;
        }

        /* Summary box */
        .summary-box {
            border: 1px solid #e5e7eb;
            padding: 10px;
            margin-top: 15px;
        }
        .summary-row { margin-bottom: 4px; }
        .summary-row table { width: 100%; }
        .summary-row td { font-size: 8.5pt; padding: 2px 0; }
        .summary-row .label { color: #6b7280; }
        .summary-row .value { text-align: right; font-weight: bold; }
        .summary-total td {
            border-top: 2px solid #2563eb;
            padding-top: 6px;
            font-size: 10pt;
            font-weight: bold;
            color: #1e40af;
        }

        /* Info box */
        .info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-size: 8pt;
            color: #4b5563;
        }
        .info-box strong { color: #1f2937; }

        /* Result highlight */
        .result-positive { color: #b91c1c; }
        .result-negative { color: #15803d; }

        /* Footer */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            font-size: 7pt;
            color: #9ca3af;
        }
        .footer table { width: 100%; }

        /* Disclaimer */
        .disclaimer {
            font-size: 7pt;
            color: #9ca3af;
            font-style: italic;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        <div class="header">
            <table>
                <tr>
                    <td style="width: 60%;">
                        <div class="company-name">{{ $company->legal_name ?? '' }}</div>
                        <div class="company-info">
                            @if($company->nif ?? false){{ __('reports.col_nif') }}: {{ $company->nif }}<br>@endif
                            @if($company->address ?? false){{ $company->address }}<br>@endif
                            @if(($company->postal_code ?? false) || ($company->city ?? false))
                                {{ $company->postal_code ?? '' }} {{ $company->city ?? '' }}
                                @if($company->province ?? false) ({{ $company->province }})@endif
                                <br>
                            @endif
                            @if($company->phone ?? false){{ $company->phone }} @endif
                            @if($company->email ?? false){{ $company->email }}@endif
                        </div>
                    </td>
                    <td style="width: 40%; text-align: right;">
                        @if($company->logo_path ?? false)
                            <img src="{{ \Storage::disk('local')->path($company->logo_path) }}" style="max-width: 140px; max-height: 50px;">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- Title --}}
        <div class="report-title">@yield('title')</div>
        @hasSection('subtitle')
            <div class="report-subtitle">@yield('subtitle')</div>
        @endif

        {{-- Content --}}
        @yield('content')

        {{-- Footer --}}
        <div class="footer">
            <table>
                <tr>
                    <td>{{ $company->legal_name ?? '' }} — {{ $company->nif ?? '' }}</td>
                    <td style="text-align: right;">{{ __('reports.generated_on') }}: {{ $generatedAt }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
