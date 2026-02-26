{{-- Lines table --}}
@php
    $columns = $options['columns'] ?? ['concept', 'qty', 'price', 'discount', 'vat', 'amount'];
    $showDescription = $options['show_description'] ?? true;

    $colWidths = [
        'concept' => '40%',
        'qty' => '10%',
        'price' => '13%',
        'discount' => '10%',
        'vat' => '10%',
        'amount' => '17%',
        'unit' => '8%',
    ];

    $colHeaders = [
        'concept' => 'Concepto',
        'qty' => 'Cantidad',
        'price' => 'Precio ud.',
        'discount' => 'Dto.',
        'vat' => 'IVA',
        'amount' => 'Importe',
        'unit' => 'Unidad',
    ];
@endphp
<table class="lines-table">
    <thead>
        <tr>
            @foreach($columns as $col)
                <th @if($col !== 'concept') class="text-right" @endif style="width: {{ $colWidths[$col] ?? 'auto' }};">{{ $colHeaders[$col] ?? $col }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($lines as $line)
            <tr>
                @foreach($columns as $col)
                    @if($col === 'concept')
                        <td>
                            <div class="concept">{{ $line->concept }}</div>
                            @if($showDescription && $line->description)
                                <div class="description">{{ $line->description }}</div>
                            @endif
                        </td>
                    @elseif($col === 'qty')
                        <td class="text-right">{{ number_format((float)$line->quantity, 2, ',', '.') }}</td>
                    @elseif($col === 'unit')
                        <td class="text-right">{{ $line->unit ?? '' }}</td>
                    @elseif($col === 'price')
                        <td class="text-right">{{ number_format((float)$line->unit_price, 2, ',', '.') }} €</td>
                    @elseif($col === 'discount')
                        <td class="text-right">
                            @if((float)$line->discount_percent > 0)
                                {{ number_format((float)$line->discount_percent, 0) }}%
                            @else
                                -
                            @endif
                        </td>
                    @elseif($col === 'vat')
                        <td class="text-right">{{ number_format((float)$line->vat_rate, 0) }}%</td>
                    @elseif($col === 'amount')
                        <td class="text-right">{{ number_format((float)$line->line_total, 2, ',', '.') }} €</td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
