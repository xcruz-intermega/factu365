<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'legal_name',
        'trade_name',
        'nif',
        'address_street',
        'address_city',
        'address_postal_code',
        'address_province',
        'address_country',
        'phone',
        'email',
        'website',
        'tax_regime',
        'irpf_rate',
        'logo_path',
        'software_id',
        'software_name',
        'software_version',
        'software_nif',
    ];

    protected function casts(): array
    {
        return [
            'irpf_rate' => 'decimal:2',
        ];
    }
}
