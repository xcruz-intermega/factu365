@extends('pdf.reports._layout')

@section('title', __('reports.modelo_347_full'))
@section('subtitle', __('reports.declarant') . ' ' . ($company->legal_name ?? '') . ' (' . ($company->nif ?? '') . ') — ' . __('reports.year_label') . ' ' . $filters['year'])

@section('content')
    <div class="info-box">{{ __('reports.threshold_notice') }}</div>

    {{-- Section A: Sales --}}
    <div class="section-header">{{ __('reports.section_sales') }}</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('reports.col_nif') }}</th>
                <th>{{ __('reports.col_client') }}</th>
                <th class="text-right">{{ __('reports.col_annual_total') }}</th>
                <th class="text-right">{{ __('reports.col_q1') }}</th>
                <th class="text-right">{{ __('reports.col_q2') }}</th>
                <th class="text-right">{{ __('reports.col_q3') }}</th>
                <th class="text-right">{{ __('reports.col_q4') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $row)
                <tr>
                    <td>{{ $row['nif'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($row['annual_total'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($row['q1'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($row['q2'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($row['q3'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($row['q4'], 2, ',', '.') }} &euro;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #9ca3af;">{{ __('reports.no_operations_347') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($sales) > 0)
            <tfoot>
                <tr>
                    <td colspan="2">{{ __('reports.total_section') }}</td>
                    <td class="text-right">{{ number_format(collect($sales)->sum('annual_total'), 2, ',', '.') }} &euro;</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Section B: Purchases --}}
    <div class="section-header">{{ __('reports.section_purchases') }}</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('reports.col_nif') }}</th>
                <th>{{ __('books.col_supplier') }}</th>
                <th class="text-right">{{ __('reports.col_annual_total') }}</th>
                <th class="text-right">{{ __('reports.col_q1') }}</th>
                <th class="text-right">{{ __('reports.col_q2') }}</th>
                <th class="text-right">{{ __('reports.col_q3') }}</th>
                <th class="text-right">{{ __('reports.col_q4') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $row)
                <tr>
                    <td>{{ $row['nif'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($row['annual_total'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($row['q1'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($row['q2'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($row['q3'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($row['q4'], 2, ',', '.') }} &euro;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #9ca3af;">{{ __('reports.no_operations_347') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($purchases) > 0)
            <tfoot>
                <tr>
                    <td colspan="2">{{ __('reports.total_section') }}</td>
                    <td class="text-right">{{ number_format(collect($purchases)->sum('annual_total'), 2, ',', '.') }} &euro;</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="disclaimer">{{ __('reports.fiscal_disclaimer') }}</p>
@endsection
