<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'max_invoices',
        'max_users',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'max_invoices' => 'integer',
            'max_users' => 'integer',
        ];
    }
}
