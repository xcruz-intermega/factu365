@extends('pdf.reports._layout')

@section('title', __('reports.modelo_111_full'))
@section('subtitle', __('reports.declarant') . ' ' . ($company->legal_name ?? '') . ' (' . ($company->nif ?? '') . ') — ' . __('reports.period') . ' ' . $filters['year'] . ' - ' . $filters['quarter'] . 'T')

@section('content')
    <div class="section-header">{{ __('reports.section_professional') }}</div>

    @if($data['recipients'] > 0)
        <div class="summary-box">
            <table style="width: 100%;">
                <tr>
                    <td style="font-size: 9pt; color: #6b7280; padding: 4px 0;">{{ __('reports.casilla_04_recipients') }}</td>
                    <td style="text-align: right; font-size: 9pt; font-weight: bold;">{{ $data['recipients'] }}</td>
                </tr>
                <tr>
                    <td style="font-size: 9pt; color: #6b7280; padding: 4px 0;">{{ __('reports.casilla_05_base') }}</td>
                    <td style="text-align: right; font-size: 9pt; font-weight: bold;">{{ number_format($data['base'], 2, ',', '.') }} &euro;</td>
                </tr>
                <tr>
                    <td style="font-size: 9pt; color: #6b7280; padding: 4px 0;">{{ __('reports.casilla_06_withheld') }}</td>
                    <td style="text-align: right; font-size: 9pt; font-weight: bold;">{{ number_format($data['withheld'], 2, ',', '.') }} &euro;</td>
                </tr>
                <tr class="summary-total">
                    <td style="border-top: 2px solid #2563eb; padding-top: 6px; font-size: 10pt; font-weight: bold;">{{ __('reports.result_to_pay') }}</td>
                    <td style="border-top: 2px solid #2563eb; padding-top: 6px; text-align: right; font-size: 10pt; font-weight: bold; color: #b91c1c;">
                        {{ number_format($data['to_pay'], 2, ',', '.') }} &euro;
                    </td>
                </tr>
            </table>
        </div>
    @else
        <p style="text-align: center; color: #9ca3af; padding: 20px 0;">{{ __('reports.no_retentions') }}</p>
    @endif

    <p class="disclaimer">{{ __('reports.fiscal_disclaimer') }}</p>
@endsection
