<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:expense_categories,id'],
            'supplier_client_id' => ['nullable', 'exists:clients,id'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'expense_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'concept' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'irpf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'irpf_type' => [Rule::requiredIf(fn () => (float) $this->input('irpf_rate', 0) > 0), 'nullable', 'in:professional,rental,other'],
            'payment_status' => ['nullable', 'in:pending,paid'],
            'payment_date' => ['nullable', 'date', 'required_if:payment_status,paid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'expense_date.required' => 'La fecha del gasto es obligatoria.',
            'concept.required' => 'El concepto es obligatorio.',
            'subtotal.required' => 'El importe es obligatorio.',
            'subtotal.min' => 'El importe debe ser mayor o igual a cero.',
            'vat_rate.required' => 'El tipo de IVA es obligatorio.',
            'payment_date.required_if' => 'La fecha de pago es obligatoria cuando el estado es pagado.',
            'attachment.max' => 'El archivo adjunto no puede superar los 10 MB.',
            'attachment.mimes' => 'El adjunto debe ser PDF, JPG, PNG o WebP.',
        ];
    }
}
