@extends('pdf.reports._layout')

@section('title', __('reports.modelo_130_full'))
@section('subtitle', __('reports.declarant') . ' ' . ($company->legal_name ?? '') . ' (' . ($company->nif ?? '') . ') — ' . __('reports.period') . ' ' . $filters['year'] . ' - ' . $filters['quarter'] . 'T')

@section('content')
    <div class="section-header">{{ __('reports.section_direct_estimation') }}</div>

    <table class="report-table">
        <tbody>
            {{-- Row 01: Revenue --}}
            <tr>
                <td>
                    <strong>{{ __('reports.row_01_income') }}</strong><br>
                    <span style="font-size: 7pt; color: #6b7280;">{{ __('reports.row_01_subtitle') }}</span>
                </td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($data['revenue'], 2, ',', '.') }} &euro;</td>
            </tr>
            {{-- Row 02: Expenses --}}
            <tr>
                <td>
                    <strong>{{ __('reports.row_02_expenses') }}</strong><br>
                    <span style="font-size: 7pt; color: #6b7280;">{{ __('reports.row_02_subtitle') }}</span>
                </td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($data['deductible_expenses'], 2, ',', '.') }} &euro;</td>
            </tr>
            {{-- Row 03: Net income --}}
            <tr style="background-color: #f3f4f6;">
                <td><strong>{{ __('reports.row_03_net') }}</strong></td>
                <td class="text-right" style="font-weight: bold; color: {{ $data['net_income'] >= 0 ? '#1f2937' : '#b91c1c' }};">
                    {{ number_format($data['net_income'], 2, ',', '.') }} &euro;
                </td>
            </tr>
            {{-- Row 04: IRPF rate --}}
            <tr>
                <td><strong>{{ __('reports.row_04_pct', ['rate' => $data['irpf_rate']]) }}</strong></td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($data['irpf_payment'], 2, ',', '.') }} &euro;</td>
            </tr>
            {{-- Row 05: Retentions --}}
            <tr>
                <td>
                    <strong>{{ __('reports.row_05_withholdings') }}</strong><br>
                    <span style="font-size: 7pt; color: #6b7280;">{{ __('reports.row_05_subtitle') }}</span>
                </td>
                <td class="text-right" style="font-weight: bold; color: #15803d;">- {{ number_format($data['retentions'], 2, ',', '.') }} &euro;</td>
            </tr>
            {{-- Row 06: Previous payments --}}
            <tr>
                <td><strong>{{ __('reports.row_06_previous') }}</strong></td>
                <td class="text-right" style="font-weight: bold; color: #15803d;">- {{ number_format($data['previous_payments'], 2, ',', '.') }} &euro;</td>
            </tr>
            {{-- Row 07: Result --}}
            <tr style="background-color: #eff6ff;">
                <td style="font-size: 10pt; font-weight: bold; color: #1e40af;">{{ __('reports.row_07_total') }}</td>
                <td class="text-right" style="font-size: 10pt; font-weight: bold; color: {{ $data['to_pay'] > 0 ? '#b91c1c' : '#15803d' }};">
                    {{ number_format($data['to_pay'], 2, ',', '.') }} &euro;
                </td>
            </tr>
        </tbody>
    </table>

    <p class="disclaimer">{{ __('reports.fiscal_disclaimer') }}</p>
@endsection
