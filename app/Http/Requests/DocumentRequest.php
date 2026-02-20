<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::in([
                Document::TYPE_INVOICE,
                Document::TYPE_QUOTE,
                Document::TYPE_DELIVERY_NOTE,
                Document::TYPE_PROFORMA,
                Document::TYPE_RECEIPT,
                Document::TYPE_RECTIFICATIVE,
                Document::TYPE_PURCHASE_INVOICE,
            ])],
            'invoice_type' => ['nullable', Rule::in([
                Document::INVOICE_TYPE_F1,
                Document::INVOICE_TYPE_F2,
                Document::INVOICE_TYPE_F3,
                Document::INVOICE_TYPE_R1,
                Document::INVOICE_TYPE_R2,
                Document::INVOICE_TYPE_R3,
                Document::INVOICE_TYPE_R4,
                Document::INVOICE_TYPE_R5,
            ])],
            'client_id' => ['nullable', 'exists:clients,id'],
            'series_id' => ['nullable', 'exists:document_series,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'operation_date' => ['nullable', 'date'],
            'global_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'regime_key' => ['nullable', 'string', 'max:5'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'footer_text' => ['nullable', 'string', 'max:2000'],
            'corrected_document_id' => ['nullable', 'exists:documents,id'],
            'rectificative_type' => ['nullable', Rule::in(['substitution', 'differences'])],

            // Lines
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['nullable', 'exists:products,id'],
            'lines.*.concept' => ['required', 'string', 'max:500'],
            'lines.*.description' => ['nullable', 'string', 'max:2000'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.unit' => ['nullable', 'string', 'max:20'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.vat_rate' => ['required', 'numeric', Rule::in([0, 4, 10, 21])],
            'lines.*.exemption_code' => ['nullable', 'string', 'required_if:lines.*.vat_rate,0'],
            'lines.*.irpf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.surcharge_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'Debe añadir al menos una línea.',
            'lines.min' => 'Debe añadir al menos una línea.',
            'lines.*.concept.required' => 'El concepto es obligatorio en cada línea.',
            'lines.*.quantity.required' => 'La cantidad es obligatoria.',
            'lines.*.quantity.min' => 'La cantidad debe ser mayor que cero.',
            'lines.*.unit_price.required' => 'El precio unitario es obligatorio.',
            'lines.*.vat_rate.required' => 'El tipo de IVA es obligatorio.',
            'lines.*.exemption_code.required_if' => 'El código de exención es obligatorio cuando el IVA es 0%.',
            'issue_date.required' => 'La fecha de emisión es obligatoria.',
            'due_date.after_or_equal' => 'La fecha de vencimiento debe ser posterior a la de emisión.',
        ];
    }
}
