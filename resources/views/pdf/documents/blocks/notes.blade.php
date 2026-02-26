{{-- Notes --}}
@if($document->notes)
    <div class="notes">
        <div class="notes-title">{{ __('pdf.notes') }}</div>
        {{ $document->notes }}
    </div>
@endif
