{{-- Page footer --}}
@php
    $content = $options['content'] ?? 'company';
@endphp
<div class="page-footer">
    @if($content === 'company')
        {{ $company?->legal_name ?? '' }} &middot; NIF: {{ $company?->nif ?? '' }}
        @if($company?->website) &middot; {{ $company->website }}@endif
    @else
        {{ $content }}
    @endif
</div>
