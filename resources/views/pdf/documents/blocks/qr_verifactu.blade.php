{{-- QR Code (VeriFactu) --}}
@if(($settings['show_qr'] ?? true) && $qrDataUri)
    @php
        $position = $options['position'] ?? 'left';
        $size = ($options['size'] ?? 80) . 'px';
        $showLegalText = $options['show_legal_text'] ?? true;
    @endphp
    <div class="qr-section">
        <table>
            <tr>
                @if($position === 'right')
                    <td>
                        @if($showLegalText)
                            <div class="qr-text" style="padding-left: 0; padding-right: 10px;">
                                <strong>Código QR VERI*FACTU</strong><br>
                                Factura verificable conforme al RD 1007/2023.<br>
                                Escanee el código QR para verificar la autenticidad de esta factura<br>
                                en la Agencia Tributaria.
                            </div>
                        @endif
                    </td>
                    <td style="width: {{ intval($size) + 10 }}px; text-align: right;">
                        <img src="{{ $qrDataUri }}" style="width: {{ $size }}; height: {{ $size }};" alt="QR VeriFactu">
                    </td>
                @else
                    <td style="width: {{ intval($size) + 10 }}px;">
                        <img src="{{ $qrDataUri }}" style="width: {{ $size }}; height: {{ $size }};" alt="QR VeriFactu">
                    </td>
                    <td>
                        @if($showLegalText)
                            <div class="qr-text">
                                <strong>Código QR VERI*FACTU</strong><br>
                                Factura verificable conforme al RD 1007/2023.<br>
                                Escanee el código QR para verificar la autenticidad de esta factura<br>
                                en la Agencia Tributaria.
                            </div>
                        @endif
                    </td>
                @endif
            </tr>
        </table>
    </div>
@endif
