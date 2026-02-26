{{-- Legal text --}}
<div class="legal-text">
    @if(in_array($document->document_type, ['invoice', 'rectificative']))
        {{ __('pdf.legal_verifactu') }}
        @if($company?->software_name)
            Software: {{ $company->software_name }} v{{ $company->software_version ?? '1.0' }}.
        @endif
    @endif
</div>
