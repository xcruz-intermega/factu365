@extends('pdf.reports._layout')

@section('title', __('reports.sales_by_product_full'))
@section('subtitle', __('reports.pdf_period_label', ['from' => $filters['date_from'], 'to' => $filters['date_to']]))

@section('content')
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('reports.col_product_concept') }}</th>
                <th class="text-right">{{ __('reports.col_quantity') }}</th>
                <th class="text-right">{{ __('common.tax_base') }}</th>
                <th class="text-right">{{ __('common.vat') }}</th>
                <th class="text-right">{{ __('common.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>
                        {{ $row->product->name ?? $row->concept }}
                        @if($row->product->reference ?? false)
                            <span style="color: #9ca3af; font-size: 7pt;">({{ $row->product->reference }})</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format((float) $row->total_quantity, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format((float) $row->total_subtotal, 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row->total_vat, 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row->total_amount, 2, ',', '.') }} &euro;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #9ca3af;">{{ __('reports.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
            <tfoot>
                <tr>
                    <td colspan="2">{{ __('common.total') }}</td>
                    <td class="text-right">{{ number_format($totals['total_subtotal'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total_vat'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total_amount'], 2, ',', '.') }} &euro;</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
