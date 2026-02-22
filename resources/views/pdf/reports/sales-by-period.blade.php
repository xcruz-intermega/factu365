@extends('pdf.reports._layout')

@section('title', __('reports.sales_by_period'))
@section('subtitle', __('reports.pdf_period_label', ['from' => $filters['date_from'], 'to' => $filters['date_to']]))

@section('content')
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('reports.col_period') }}</th>
                <th class="text-right">{{ __('reports.col_invoices') }}</th>
                <th class="text-right">{{ __('common.tax_base') }}</th>
                <th class="text-right">{{ __('common.vat') }}</th>
                <th class="text-right">{{ __('common.irpf') }}</th>
                <th class="text-right">{{ __('common.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="text-right">{{ $row['invoice_count'] }}</td>
                    <td class="text-right">{{ number_format((float) $row['total_base'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['total_vat'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['total_irpf'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['total_amount'], 2, ',', '.') }} &euro;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #9ca3af;">{{ __('reports.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
            <tfoot>
                <tr>
                    <td>{{ __('common.total') }}</td>
                    <td class="text-right">{{ $totals['invoice_count'] }}</td>
                    <td class="text-right">{{ number_format($totals['total_base'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total_vat'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total_irpf'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total_amount'], 2, ',', '.') }} &euro;</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
