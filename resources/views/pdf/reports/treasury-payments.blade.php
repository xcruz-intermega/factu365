@extends('pdf.reports._layout')

@section('title', __('treasury.payments_title'))
@if($filters['date_from'] || $filters['date_to'])
    @section('subtitle', __('reports.pdf_period_label', ['from' => $filters['date_from'] ?? '...', 'to' => $filters['date_to'] ?? '...']))
@endif

@section('content')
    {{-- Section 1: Pending expenses --}}
    <div class="section-header">{{ __('treasury.pending_expenses') }}</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('treasury.due_date') }}</th>
                <th>{{ __('treasury.concept') }}</th>
                <th>{{ __('treasury.supplier') }}</th>
                <th class="text-right">{{ __('treasury.amount') }}</th>
                <th class="text-right">{{ __('treasury.days_overdue') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenseItems as $item)
                <tr>
                    <td style="{{ $item['is_overdue'] ? 'color:#b91c1c;font-weight:bold;' : '' }}">{{ $item['due_date'] ? \Carbon\Carbon::parse($item['due_date'])->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item['concept'] }}</td>
                    <td>{{ $item['supplier_name'] }}</td>
                    <td class="text-right">{{ number_format($item['amount'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right" style="{{ $item['is_overdue'] ? 'color:#b91c1c;font-weight:bold;' : '' }}">{{ $item['days_overdue'] > 0 ? $item['days_overdue'] : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#9ca3af;">{{ __('treasury.no_pending_payments') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($expenseItems) > 0)
            <tfoot>
                <tr>
                    <td colspan="3">{{ __('treasury.total_pending') }}</td>
                    <td class="text-right">{{ number_format($expenseTotalPending, 2, ',', '.') }} &euro;</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Section 2: Pending purchase invoices --}}
    <div class="section-header">{{ __('treasury.pending_purchase_invoices') }}</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>{{ __('treasury.due_date') }}</th>
                <th>{{ __('treasury.document_number') }}</th>
                <th>{{ __('treasury.supplier') }}</th>
                <th class="text-right">{{ __('treasury.amount') }}</th>
                <th class="text-right">{{ __('treasury.days_overdue') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseItems as $item)
                <tr>
                    <td style="{{ $item['is_overdue'] ? 'color:#b91c1c;font-weight:bold;' : '' }}">{{ \Carbon\Carbon::parse($item['due_date'])->format('d/m/Y') }}</td>
                    <td>{{ $item['document_number'] }}</td>
                    <td>{{ $item['supplier_name'] }}</td>
                    <td class="text-right">{{ number_format($item['amount'], 2, ',', '.') }} &euro;</td>
                    <td class="text-right" style="{{ $item['is_overdue'] ? 'color:#b91c1c;font-weight:bold;' : '' }}">{{ $item['days_overdue'] > 0 ? $item['days_overdue'] : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#9ca3af;">{{ __('treasury.no_pending_payments') }}</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($purchaseItems) > 0)
            <tfoot>
                <tr>
                    <td colspan="3">{{ __('treasury.total_pending') }}</td>
                    <td class="text-right">{{ number_format($purchaseTotalPending, 2, ',', '.') }} &euro;</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Grand totals --}}
    @if(count($expenseItems) > 0 || count($purchaseItems) > 0)
        <div class="summary-box">
            <div class="summary-row">
                <table>
                    <tr class="summary-total">
                        <td class="label">{{ __('treasury.total_general') }}</td>
                        <td class="value">{{ number_format($totalPending, 2, ',', '.') }} &euro;</td>
                    </tr>
                    @if($totalOverdue > 0)
                        <tr>
                            <td class="label" style="color:#b91c1c;">{{ __('treasury.total_overdue') }}</td>
                            <td class="value" style="color:#b91c1c;">{{ number_format($totalOverdue, 2, ',', '.') }} &euro;</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    @endif
@endsection
