{{-- Legal text --}}
<div class="legal-text">
    @if(in_array($document->document_type, ['invoice', 'rectificative']))
        Factura generada por sistema informático conforme al Reglamento VERI*FACTU (RD 1007/2023).
        @if($company?->software_name)
            Software: {{ $company->software_name }} v{{ $company->software_version ?? '1.0' }}.
        @endif
    @endif
</div>
