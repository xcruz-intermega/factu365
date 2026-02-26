{{-- Header: Logo + Company Info --}}
<div class="header">
    <table>
        <tr>
            @php
                $logoPos = $options['logo_position'] ?? 'left';
                $showLogo = $options['show_logo'] ?? true;
                $showCompanyInfo = $options['show_company_info'] ?? true;
            @endphp

            @if($logoPos === 'right')
                {{-- Company info left, logo right --}}
                @if($showCompanyInfo)
                    <td style="width: 50%;">
                        <span class="company-name">{{ $company?->trade_name ?: $company?->legal_name ?? '' }}</span>
                        <div class="company-info">
                            @if($company)
                                <strong>{{ $company->legal_name }}</strong><br>
                                NIF: {{ $company->nif }}<br>
                                @if($company->address_street){{ $company->address_street }}<br>@endif
                                @if($company->address_postal_code || $company->address_city)
                                    {{ $company->address_postal_code }} {{ $company->address_city }}
                                    @if($company->address_province)({{ $company->address_province }})@endif
                                    <br>
                                @endif
                                @if($company->phone)Tel: {{ $company->phone }}<br>@endif
                                @if($company->email){{ $company->email }}<br>@endif
                            @endif
                        </div>
                    </td>
                @endif
                <td style="width: {{ $showCompanyInfo ? '50' : '100' }}%; text-align: right;">
                    @if($showLogo && $company?->logo_path)
                        <img src="{{ storage_path('app/private/' . $company->logo_path) }}" class="logo" alt="Logo">
                    @endif
                </td>
            @elseif($logoPos === 'center')
                {{-- Logo centered above --}}
                <td style="width: 100%; text-align: center;">
                    @if($showLogo && $company?->logo_path)
                        <img src="{{ storage_path('app/private/' . $company->logo_path) }}" class="logo" alt="Logo">
                        <br>
                    @endif
                    <span class="company-name">{{ $company?->trade_name ?: $company?->legal_name ?? '' }}</span>
                    @if($showCompanyInfo)
                        <div class="company-info" style="text-align: center;">
                            @if($company)
                                <strong>{{ $company->legal_name }}</strong><br>
                                NIF: {{ $company->nif }}<br>
                                @if($company->address_street){{ $company->address_street }}<br>@endif
                                @if($company->address_postal_code || $company->address_city)
                                    {{ $company->address_postal_code }} {{ $company->address_city }}
                                    @if($company->address_province)({{ $company->address_province }})@endif
                                    <br>
                                @endif
                                @if($company->phone)Tel: {{ $company->phone }}<br>@endif
                                @if($company->email){{ $company->email }}<br>@endif
                            @endif
                        </div>
                    @endif
                </td>
            @else
                {{-- Default: logo left, company info right --}}
                <td style="width: 50%;">
                    @if($showLogo && $company?->logo_path)
                        <img src="{{ storage_path('app/private/' . $company->logo_path) }}" class="logo" alt="Logo">
                        <br>
                    @endif
                    <span class="company-name">{{ $company?->trade_name ?: $company?->legal_name ?? '' }}</span>
                </td>
                @if($showCompanyInfo)
                    <td style="width: 50%; text-align: right;">
                        <div class="company-info">
                            @if($company)
                                <strong>{{ $company->legal_name }}</strong><br>
                                NIF: {{ $company->nif }}<br>
                                @if($company->address_street){{ $company->address_street }}<br>@endif
                                @if($company->address_postal_code || $company->address_city)
                                    {{ $company->address_postal_code }} {{ $company->address_city }}
                                    @if($company->address_province)({{ $company->address_province }})@endif
                                    <br>
                                @endif
                                @if($company->phone)Tel: {{ $company->phone }}<br>@endif
                                @if($company->email){{ $company->email }}<br>@endif
                            @endif
                        </div>
                    </td>
                @endif
            @endif
        </tr>
    </table>
</div>
