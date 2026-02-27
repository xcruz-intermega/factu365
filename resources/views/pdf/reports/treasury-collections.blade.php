@extends('pdf.reports._layout')

@section('title', __('treasury.collections_title'))
@if($filters['date_from'] || $filters['date_to'])
    @section('subtitle', __('reports.pdf_period_label', ['from' => $filters['date_from'] ?? '...', 'to' => $filters['date_to'] ?? '...']))
@endif

@section('content')
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('treasury.due_date') }}</th>
                <th>{{ __('treasury.document_number') }}</th>
                <th>{{ __('treasury.client') }}</th>
                <th>{{ __('treasury.nif') }}</th>
                <th class="text-right">{{ __('treasury.amount') }}</th>
                <th class="text-right">{{ __('treasury.days_overdue') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td style="{{ $item['is_overdue'] ? 'color:#b91c1c;font-weight:bold;' : '' }}">{{ \Carbon\Carbon::parse($item['due_date'])->format('d/m/Y') }}</td>
                    <td>{{ $item['document_number'] }}</td>
                    <td>{{ $item['client_name'] }}</td>
                    <td>{{ $item['client_nif'] }}</td>
                    <td class="text-right">{{ number_format($item['amount'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right" style="{{ $item['is_overdue'] ? 'color:#b91c1c;font-weight:bold;' : '' }}">{{ $item['days_overdue'] > 0 ? $item['days_overdue'] : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#9ca3af;">{{ __('treasury.no_pending_collections') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($items) > 0)
            <tfoot>
                <tr>
                    <td colspan="4">{{ __('treasury.total_pending') }}</td>
                    <td class="text-right">{{ number_format($totalPending, 2, ',', '.') }} &euro;</td>
                    <td></td>
                </tr>
                @if($totalOverdue > 0)
                    <tr>
                        <td colspan="4" style="color:#b91c1c;">{{ __('treasury.total_overdue') }}</td>
                        <td class="text-right" style="color:#b91c1c;">{{ number_format($totalOverdue, 2, ',', '.') }} &euro;</td>
                        <td></td>
                    </tr>
                @endif
            </tfoot>
        @endif
    </table>
@endsection
