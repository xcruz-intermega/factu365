{{-- Notes --}}
@if($document->notes)
    <div class="notes">
        <div class="notes-title">Observaciones</div>
        {{ $document->notes }}
    </div>
@endif
