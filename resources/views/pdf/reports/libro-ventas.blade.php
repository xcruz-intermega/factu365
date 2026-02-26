@extends('pdf.reports._layout')

@section('title', __('books.libro_ventas'))
@section('subtitle', __('books.pdf_period_label', ['from' => $filters['date_from'], 'to' => $filters['date_to']]))

@section('content')
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('books.col_date') }}</th>
                <th>{{ __('books.col_number') }}</th>
                <th>{{ __('books.col_series') }}</th>
                <th>{{ __('books.col_client') }}</th>
                <th>{{ __('books.col_nif') }}</th>
                <th class="text-right">{{ __('books.col_base') }}</th>
                <th class="text-right">{{ __('books.col_vat') }}</th>
                <th class="text-right">{{ __('books.col_irpf') }}</th>
                <th class="text-right">{{ __('books.col_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $currentMonth = null; @endphp
            @forelse($data as $row)
                @if($currentMonth !== null && $currentMonth !== $row['month_key'])
                    {{-- Monthly subtotal for previous month --}}
                    @php
                        $monthRows = collect($data)->filter(fn($r) => $r['month_key'] === $currentMonth);
                    @endphp
                    <tr style="background-color: #eff6ff;">
                        <td colspan="5" style="font-weight: bold; color: #1e40af; font-size: 8pt;">
                            {{ __('books.monthly_subtotal', ['month' => $monthRows->first()['month_label']]) }}
                        </td>
                        <td class="text-right" style="font-weight: bold; color: #1e40af;">{{ number_format($monthRows->sum('tax_base'), 2, ',', '.') }} &euro;</td>
                        <td class="text-right" style="font-weight: bold; color: #1e40af;">{{ number_format($monthRows->sum('total_vat'), 2, ',', '.') }} &euro;</td>
                        <td class="text-right" style="font-weight: bold; color: #b91c1c;">{{ number_format($monthRows->sum('total_irpf'), 2, ',', '.') }} &euro;</td>
                        <td class="text-right" style="font-weight: bold; color: #1e40af;">{{ number_format($monthRows->sum('total'), 2, ',', '.') }} &euro;</td>
                    </tr>
                @endif
                @php $currentMonth = $row['month_key']; @endphp
                <tr>
                    <td>{{ $row['issue_date'] }}</td>
                    <td>{{ $row['number'] }}</td>
                    <td>{{ $row['series_name'] ?? '' }}</td>
                    <td>{{ $row['client_name'] }}</td>
                    <td>{{ $row['client_nif'] }}</td>
                    <td class="text-right">{{ number_format((float) $row['tax_base'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['total_vat'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['total_irpf'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format((float) $row['total'], 2, ',', '.') }} &euro;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #9ca3af;">{{ __('books.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
            <tfoot>
                <tr>
                    <td colspan="5">{{ __('books.total_period') }}</td>
                    <td class="text-right">{{ number_format($totals['tax_base'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total_vat'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total_irpf'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right">{{ number_format($totals['total'], 2, ',', '.') }} &euro;</td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
