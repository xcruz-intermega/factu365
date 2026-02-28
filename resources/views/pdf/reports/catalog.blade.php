@extends('pdf.reports._layout')

@section('title', __('products.catalog_pdf_title'))

@section('content')
    @foreach($families as $family)
        @if($family->products->count() > 0)
            <div class="section-header">{{ $family->name }}</div>

            <table class="report-table">
                <thead>
                    <tr>
                        @if($showImages)
                            <th style="width: 55px;">{{ __('products.col_image') }}</th>
                        @endif
                        <th>{{ __('products.col_ref') }}</th>
                        <th>{{ __('products.col_name') }}</th>
                        <th>{{ __('products.description') }}</th>
                        @if($showPrices)
                            <th class="text-right">{{ __('products.col_price') }}</th>
                            <th class="text-right">{{ __('products.col_vat') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($family->products as $product)
                        <tr>
                            @if($showImages)
                                <td>
                                    @if($product->image_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($product->image_path))
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('local')->path($product->image_path) }}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;">
                                    @else
                                        <div style="width: 45px; height: 45px; background: #f3f4f6; border-radius: 4px;"></div>
                                    @endif
                                </td>
                            @endif
                            <td style="font-size: 8pt; color: #6b7280;">{{ $product->reference ?? '—' }}</td>
                            <td style="font-weight: bold;">{{ $product->name }}</td>
                            <td style="font-size: 7.5pt; color: #6b7280;">{{ \Illuminate\Support\Str::limit($product->description, 80) }}</td>
                            @if($showPrices)
                                <td class="text-right" style="font-weight: bold;">{{ number_format((float) $product->unit_price, 2, ',', '.') }} &euro;</td>
                                <td class="text-right">{{ number_format((float) $product->vat_rate, 0) }}%</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    @if($uncategorized->count() > 0)
        <div class="section-header">{{ __('products.no_category') }}</div>

        <table class="report-table">
            <thead>
                <tr>
                    @if($showImages)
                        <th style="width: 55px;">{{ __('products.col_image') }}</th>
                    @endif
                    <th>{{ __('products.col_ref') }}</th>
                    <th>{{ __('products.col_name') }}</th>
                    <th>{{ __('products.description') }}</th>
                    @if($showPrices)
                        <th class="text-right">{{ __('products.col_price') }}</th>
                        <th class="text-right">{{ __('products.col_vat') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($uncategorized as $product)
                    <tr>
                        @if($showImages)
                            <td>
                                @if($product->image_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($product->image_path))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('local')->path($product->image_path) }}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;">
                                @else
                                    <div style="width: 45px; height: 45px; background: #f3f4f6; border-radius: 4px;"></div>
                                @endif
                            </td>
                        @endif
                        <td style="font-size: 8pt; color: #6b7280;">{{ $product->reference ?? '—' }}</td>
                        <td style="font-weight: bold;">{{ $product->name }}</td>
                        <td style="font-size: 7.5pt; color: #6b7280;">{{ \Illuminate\Support\Str::limit($product->description, 80) }}</td>
                        @if($showPrices)
                            <td class="text-right" style="font-weight: bold;">{{ number_format((float) $product->unit_price, 2, ',', '.') }} &euro;</td>
                            <td class="text-right">{{ number_format((float) $product->vat_rate, 0) }}%</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($families->every(fn ($f) => $f->products->count() === 0) && $uncategorized->count() === 0)
        <p style="text-align: center; color: #9ca3af; margin-top: 30px;">{{ __('products.no_products') }}</p>
    @endif
@endsection
