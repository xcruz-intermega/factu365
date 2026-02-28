<?php

namespace App\Http\Requests;

use App\Models\VatRate;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:product,service',
            'product_family_id' => 'nullable|exists:product_families,id',
            'reference' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'unit_price' => 'required|numeric|min:0|max:99999999.99',
            'vat_rate' => ['required', 'numeric', \Illuminate\Validation\Rule::in($this->allowedVatRates())],
            'exemption_code' => 'nullable|string|max:10|required_if:vat_rate,0',
            'irpf_applicable' => 'boolean',
            'unit' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'allow_negative_stock' => 'boolean',
            'stock_mode' => 'nullable|in:self,components',
        ];
    }

    private function allowedVatRates(): array
    {
        try {
            return VatRate::pluck('rate')->map(fn ($r) => (float) $r)->toArray();
        } catch (\Throwable) {
            return [0, 4, 10, 21];
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'unit_price.required' => 'El precio es obligatorio.',
            'unit_price.min' => 'El precio no puede ser negativo.',
            'vat_rate.required' => 'El tipo de IVA es obligatorio.',
            'vat_rate.in' => 'El tipo de IVA debe ser 0%, 4%, 10% o 21%.',
            'exemption_code.required_if' => 'El código de exención es obligatorio cuando el IVA es 0%.',
        ];
    }
}
