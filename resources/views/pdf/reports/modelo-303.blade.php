@extends('pdf.reports._layout')

@section('title', __('reports.modelo_303_full'))
@section('subtitle', __('reports.declarant') . ' ' . ($company->legal_name ?? '') . ' (' . ($company->nif ?? '') . ') — ' . __('reports.period') . ' ' . $filters['year'] . ' - ' . $filters['quarter'] . 'T')

@section('content')
    {{-- IVA Devengado --}}
    <div class="section-header">{{ __('reports.vat_charged') }}</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('reports.vat_type') }}</th>
                <th class="text-right">{{ __('reports.col_base') }}</th>
                <th class="text-right">{{ __('reports.col_amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vatIssued as $row)
                <tr>
                    <td>{{ $row->vat_rate }}%</td>
                    <td class="text-right">{{ number_format((float) $row->base, 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row->vat, 2, ',', '.') }} &euro;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #9ca3af;">{{ __('reports.no_operations') }}</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">{{ __('reports.total_vat_charged') }}</td>
                <td class="text-right">{{ number_format($summary['total_vat_issued'], 2, ',', '.') }} &euro;</td>
            </tr>
        </tfoot>
    </table>

    {{-- IVA Deducible --}}
    <div class="section-header">{{ __('reports.vat_deductible') }}</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('reports.vat_type') }}</th>
                <th class="text-right">{{ __('reports.col_base') }}</th>
                <th class="text-right">{{ __('reports.col_amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vatReceived as $row)
                <tr>
                    <td>{{ $row['vat_rate'] }}%</td>
                    <td class="text-right">{{ number_format((float) $row['base'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['vat'], 2, ',', '.') }} &euro;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #9ca3af;">{{ __('reports.no_operations') }}</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">{{ __('reports.total_vat_deductible') }}</td>
                <td class="text-right">{{ number_format($summary['total_vat_received'], 2, ',', '.') }} &euro;</td>
            </tr>
        </tfoot>
    </table>

    {{-- Result --}}
    <div class="summary-box">
        <table style="width: 100%;">
            <tr>
                <td style="font-size: 8.5pt; color: #6b7280;">{{ __('reports.total_vat_charged') }}</td>
                <td style="text-align: right; font-size: 8.5pt; font-weight: bold;">{{ number_format($summary['total_vat_issued'], 2, ',', '.') }} &euro;</td>
            </tr>
            <tr>
                <td style="font-size: 8.5pt; color: #6b7280;">{{ __('reports.total_vat_deductible') }}</td>
                <td style="text-align: right; font-size: 8.5pt; font-weight: bold; color: #15803d;">- {{ number_format($summary['total_vat_received'], 2, ',', '.') }} &euro;</td>
            </tr>
            <tr class="summary-total">
                <td style="border-top: 2px solid #2563eb; padding-top: 6px; font-size: 10pt; font-weight: bold;">{{ __('reports.difference') }}</td>
                <td style="border-top: 2px solid #2563eb; padding-top: 6px; text-align: right; font-size: 10pt; font-weight: bold; color: {{ $summary['difference'] >= 0 ? '#b91c1c' : '#15803d' }};">
                    {{ number_format($summary['difference'], 2, ',', '.') }} &euro;
                </td>
            </tr>
        </table>
    </div>

    <p class="disclaimer">{{ __('reports.fiscal_disclaimer') }}</p>
@endsection
