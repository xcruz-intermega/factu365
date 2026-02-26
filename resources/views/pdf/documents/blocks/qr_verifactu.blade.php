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
                                <strong>{{ __('pdf.qr_title') }}</strong><br>
                                {{ __('pdf.qr_description') }}<br>
                                {{ __('pdf.qr_scan') }}
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
                                <strong>{{ __('pdf.qr_title') }}</strong><br>
                                {{ __('pdf.qr_description') }}<br>
                                {{ __('pdf.qr_scan') }}
                            </div>
                        @endif
                    </td>
                @endif
            </tr>
        </table>
    </div>
@endif
