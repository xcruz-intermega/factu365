{{-- Client + Document info --}}
@php
    $layout = $options['layout'] ?? 'side-by-side';
@endphp
<div class="info-row">
    @if($layout === 'stacked')
        {{-- Stacked: client above, document below --}}
        <div class="info-box" style="margin-bottom: 10px;">
            <div class="info-box-title">{{ __('pdf.client_data') }}</div>
            @if($document->client)
                <p class="name">{{ $document->client->trade_name ?: $document->client->legal_name }}</p>
                @if($document->client->trade_name)
                    <p>{{ $document->client->legal_name }}</p>
                @endif
                <p>NIF: {{ $document->client->nif }}</p>
                @if($document->client->address_street)
                    <p>{{ $document->client->address_street }}</p>
                @endif
                @if($document->client->address_postal_code || $document->client->address_city)
                    <p>{{ $document->client->address_postal_code }} {{ $document->client->address_city }}
                    @if($document->client->address_province) ({{ $document->client->address_province }})@endif
                    </p>
                @endif
            @else
                <p class="name" style="color: #9ca3af;">{{ __('pdf.no_client') }}</p>
            @endif
        </div>
        <div class="info-box">
            <div class="info-box-title">{{ __('pdf.document_data') }}</div>
            <p><strong>{{ __('pdf.issue_date') }}:</strong> {{ $document->issue_date?->format('d/m/Y') }}</p>
            @if($document->due_date)
                <p><strong>{{ __('pdf.due_date') }}:</strong> {{ $document->due_date->format('d/m/Y') }}</p>
            @endif
            @if($document->operation_date)
                <p><strong>{{ __('pdf.operation_date') }}:</strong> {{ $document->operation_date->format('d/m/Y') }}</p>
            @endif
            @if($document->invoice_type)
                <p><strong>{{ __('pdf.invoice_type') }}:</strong> {{ $document->invoice_type }}</p>
            @endif
            @if($document->correctedDocument)
                <p><strong>{{ __('pdf.rectifies') }}:</strong> {{ $document->correctedDocument->number }}</p>
            @endif
        </div>
    @else
        {{-- Default: side-by-side --}}
        <table>
            <tr>
                <td style="width: 55%; padding-right: 10px;">
                    <div class="info-box">
                        <div class="info-box-title">{{ __('pdf.client_data') }}</div>
                        @if($document->client)
                            <p class="name">{{ $document->client->trade_name ?: $document->client->legal_name }}</p>
                            @if($document->client->trade_name)
                                <p>{{ $document->client->legal_name }}</p>
                            @endif
                            <p>NIF: {{ $document->client->nif }}</p>
                            @if($document->client->address_street)
                                <p>{{ $document->client->address_street }}</p>
                            @endif
                            @if($document->client->address_postal_code || $document->client->address_city)
                                <p>{{ $document->client->address_postal_code }} {{ $document->client->address_city }}
                                @if($document->client->address_province) ({{ $document->client->address_province }})@endif
                                </p>
                            @endif
                        @else
                            <p class="name" style="color: #9ca3af;">{{ __('pdf.no_client') }}</p>
                        @endif
                    </div>
                </td>
                <td style="width: 45%;">
                    <div class="info-box">
                        <div class="info-box-title">{{ __('pdf.document_data') }}</div>
                        <p><strong>{{ __('pdf.issue_date') }}:</strong> {{ $document->issue_date?->format('d/m/Y') }}</p>
                        @if($document->due_date)
                            <p><strong>{{ __('pdf.due_date') }}:</strong> {{ $document->due_date->format('d/m/Y') }}</p>
                        @endif
                        @if($document->operation_date)
                            <p><strong>{{ __('pdf.operation_date') }}:</strong> {{ $document->operation_date->format('d/m/Y') }}</p>
                        @endif
                        @if($document->invoice_type)
                            <p><strong>{{ __('pdf.invoice_type') }}:</strong> {{ $document->invoice_type }}</p>
                        @endif
                        @if($document->correctedDocument)
                            <p><strong>{{ __('pdf.rectifies') }}:</strong> {{ $document->correctedDocument->number }}</p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    @endif
</div>
