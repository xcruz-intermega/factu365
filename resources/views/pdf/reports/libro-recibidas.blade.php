@extends('pdf.reports._layout')

@section('title', __('books.libro_recibidas'))
@section('subtitle', __('books.pdf_period_label', ['from' => $filters['date_from'], 'to' => $filters['date_to']]))

@section('content')
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('books.col_invoice_number') }}</th>
                <th>{{ __('books.col_date') }}</th>
                <th>{{ __('books.col_nif') }}</th>
                <th>{{ __('books.col_name') }}</th>
                <th class="text-right">{{ __('books.col_base') }}</th>
                <th class="text-right">{{ __('books.col_vat') }}</th>
                <th class="text-right">{{ __('books.col_total') }}</th>
                <th>{{ __('books.col_origin') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row['invoice_number'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['supplier_nif'] }}</td>
                    <td>{{ $row['supplier_name'] }}</td>
                    <td class="text-right">{{ number_format((float) $row['tax_base'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['total_vat'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['total'], 2, ',', '.') }} &euro;</td>
                    <td>{{ $row['origin'] }}</td>
                </tr>
                @if(!empty($row['vat_breakdown']))
                    <tr>
                        <td colspan="8" style="padding-left: 30px; font-size: 7pt; color: #6b7280; background-color: #f9fafb;">
                            {{ __('books.vat_breakdown') }}:
                            @foreach($row['vat_breakdown'] as $vb)
                                {{ $vb['vat_rate'] }}%: {{ __('books.vat_base') }} {{ number_format($vb['base'], 2, ',', '.') }}&euro; / {{ __('books.vat_amount') }} {{ number_format($vb['vat'], 2, ',', '.') }}&euro;
                                @if(!$loop->last) &nbsp;|&nbsp; @endif
                            @endforeach
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #9ca3af;">{{ __('books.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
            <tfoot>
                <tr>
                    <td colspan="4">{{ __('books.total_period') }}</td>
                    <td class="text-right">{{ number_format($totals['tax_base'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total_vat'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total'], 2, ',', '.') }} &euro;</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
