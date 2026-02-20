<?php

namespace App\Http\Requests;

use App\Rules\ValidSpanishId;
use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:customer,supplier,both',
            'legal_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'nif' => ['required', 'string', 'max:20', new ValidSpanishId],
            'address_street' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:255',
            'address_postal_code' => 'nullable|string|max:10',
            'address_province' => 'nullable|string|max:255',
            'address_country' => 'nullable|string|size:2',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'payment_terms_days' => 'nullable|integer|min:0|max:365',
            'payment_method' => 'nullable|string|in:transfer,cash,card,check,direct_debit',
            'iban' => 'nullable|string|max:34',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de cliente es obligatorio.',
            'type.in' => 'El tipo debe ser cliente, proveedor o ambos.',
            'legal_name.required' => 'La razón social es obligatoria.',
            'nif.required' => 'El NIF/CIF/NIE es obligatorio.',
        ];
    }
}
