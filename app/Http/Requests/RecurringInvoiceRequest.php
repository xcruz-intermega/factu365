<?php

namespace App\Http\Requests;

use App\Models\Document;
use App\Models\RecurringInvoice;
use App\Models\VatRate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecurringInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'client_id' => ['required', 'exists:clients,id'],
            'series_id' => ['nullable', 'exists:document_series,id'],
            'payment_template_id' => ['nullable', 'exists:payment_templates,id'],

            'invoice_type' => ['nullable', Rule::in([
                Document::INVOICE_TYPE_F1,
                Document::INVOICE_TYPE_F2,
                Document::INVOICE_TYPE_F3,
            ])],
            'regime_key' => ['nullable', 'string', 'max:5'],
            'global_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'footer_text' => ['nullable', 'string', 'max:2000'],

            // Recurrence
            'interval_value' => ['required', 'integer', 'min:1', 'max:365'],
            'interval_unit' => ['required', Rule::in(RecurringInvoice::intervalUnits())],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_occurrences' => ['nullable', 'integer', 'min:1'],

            // Behavior
            'auto_finalize' => ['boolean'],
            'auto_send_email' => ['boolean'],
            'email_recipients' => ['nullable', 'string', 'max:500'],

            // Lines
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['nullable', 'exists:products,id'],
            'lines.*.concept' => ['required', 'string', 'max:500'],
            'lines.*.description' => ['nullable', 'string', 'max:2000'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.unit' => ['nullable', 'string', 'max:20'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.vat_rate' => ['required', 'numeric', Rule::in($this->allowedVatRates())],
            'lines.*.exemption_code' => ['nullable', 'string', 'required_if:lines.*.vat_rate,0'],
            'lines.*.irpf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.surcharge_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
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
            'name.required' => __('recurring.validation_name_required'),
            'client_id.required' => __('recurring.validation_client_required'),
            'interval_value.required' => __('recurring.validation_interval_required'),
            'start_date.required' => __('recurring.validation_start_date_required'),
            'lines.required' => __('recurring.validation_lines_required'),
            'lines.min' => __('recurring.validation_lines_required'),
            'lines.*.concept.required' => __('recurring.validation_concept_required'),
            'lines.*.quantity.required' => __('recurring.validation_quantity_required'),
            'lines.*.unit_price.required' => __('recurring.validation_unit_price_required'),
            'lines.*.vat_rate.required' => __('recurring.validation_vat_rate_required'),
        ];
    }
}
