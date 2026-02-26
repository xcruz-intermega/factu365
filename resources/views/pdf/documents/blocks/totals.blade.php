{{-- Totals --}}
@php
    $highlightColor = $options['highlight_color'] ?? ($global['accent_color'] ?? '#4f46e5');
@endphp
<div class="totals-section clearfix">
    <table>
        <tr>
            <td style="width: 55%; vertical-align: top;">
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td class="label">{{ __('pdf.subtotal') }}</td>
                        <td class="value">{{ number_format((float)$document->subtotal, 2, ',', '.') }} €</td>
                    </tr>
                    @if((float)$document->total_discount > 0)
                        <tr>
                            <td class="label">{{ __('pdf.discount') }}</td>
                            <td class="value">-{{ number_format((float)$document->total_discount, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                    <tr class="subtotal-row">
                        <td class="label">{{ __('pdf.tax_base') }}</td>
                        <td class="value">{{ number_format((float)$document->tax_base, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('pdf.vat') }}</td>
                        <td class="value">{{ number_format((float)$document->total_vat, 2, ',', '.') }} €</td>
                    </tr>
                    @if((float)$document->total_surcharge > 0)
                        <tr>
                            <td class="label">{{ __('pdf.equivalence_surcharge') }}</td>
                            <td class="value">{{ number_format((float)$document->total_surcharge, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                    @if((float)$document->total_irpf > 0)
                        <tr>
                            <td class="label">{{ __('pdf.irpf_withholding') }}</td>
                            <td class="value" style="color: #dc2626;">-{{ number_format((float)$document->total_irpf, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td class="value" style="color: {{ $highlightColor }}; border-top-color: {{ $highlightColor }};">{{ number_format((float)$document->total, 2, ',', '.') }} €</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
