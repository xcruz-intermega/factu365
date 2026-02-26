{{-- Document title bar --}}
@php
    $bgColor = $options['background_color'] ?? ($global['accent_color'] ?? '#4f46e5');
    $textColor = $options['text_color'] ?? '#ffffff';
@endphp
<div class="doc-title" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    {{ $typeLabel }}
    @if($document->number)
        <span class="doc-number">{{ $document->number }}</span>
    @endif
</div>
