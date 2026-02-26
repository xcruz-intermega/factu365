{{-- VAT Breakdown --}}
@if($vatBreakdown->count() > 0)
    <table class="vat-table">
        <thead>
            <tr>
                <th>{{ __('pdf.vat_type') }}</th>
                <th class="text-right">{{ __('pdf.tax_base') }}</th>
                <th class="text-right">{{ __('pdf.vat_amount') }}</th>
                @if($vatBreakdown->sum('surcharge') > 0)
                    <th class="text-right">{{ __('pdf.surcharge') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($vatBreakdown as $vat)
                <tr>
                    <td>{{ number_format($vat['vat_rate'], 0) }}%</td>
                    <td class="text-right">{{ number_format($vat['base'], 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($vat['vat'], 2, ',', '.') }} €</td>
                    @if($vatBreakdown->sum('surcharge') > 0)
                        <td class="text-right">{{ number_format($vat['surcharge'], 2, ',', '.') }} €</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
