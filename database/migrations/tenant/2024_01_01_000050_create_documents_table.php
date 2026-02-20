<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Type & classification
            $table->string('document_type', 30)->comment('invoice, quote, delivery_note, proforma, receipt, rectificative, purchase_invoice');
            $table->string('invoice_type', 5)->nullable()->comment('F1,F2,F3 for invoices; R1-R5 for rectificatives');
            $table->enum('direction', ['issued', 'received'])->default('issued');

            // Series & numbering
            $table->foreignId('series_id')->nullable()->constrained('document_series');
            $table->string('number', 40)->nullable()->comment('Full document number, e.g. FACT-2025-00001');

            // Status
            $table->string('status', 20)->default('draft');

            // Relations
            $table->foreignId('client_id')->nullable()->constrained('clients');
            $table->foreignId('parent_document_id')->nullable()->comment('Source document for conversions');
            $table->foreignId('corrected_document_id')->nullable()->comment('Original invoice for rectificatives');
            $table->foreign('parent_document_id')->references('id')->on('documents')->nullOnDelete();
            $table->foreign('corrected_document_id')->references('id')->on('documents')->nullOnDelete();

            // Dates
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('operation_date')->nullable();

            // Amounts (stored for fast queries, calculated from lines)
            $table->decimal('subtotal', 14, 2)->default(0)->comment('Sum of line totals before tax');
            $table->decimal('total_discount', 14, 2)->default(0);
            $table->decimal('tax_base', 14, 2)->default(0)->comment('Subtotal - discounts');
            $table->decimal('total_vat', 14, 2)->default(0);
            $table->decimal('total_irpf', 14, 2)->default(0);
            $table->decimal('total_surcharge', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0)->comment('Final total: tax_base + vat - irpf + surcharge');

            // Global discount
            $table->decimal('global_discount_percent', 5, 2)->default(0);
            $table->decimal('global_discount_amount', 14, 2)->default(0);

            // Fiscal
            $table->string('regime_key', 5)->default('01')->comment('Clave régimen fiscal AEAT');
            $table->string('rectificative_type')->nullable()->comment('substitution or difference');

            // VeriFactu
            $table->string('verifactu_status', 20)->nullable()->comment('pending, submitted, accepted, rejected');
            $table->text('qr_code_url')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('footer_text')->nullable();

            $table->timestamps();

            $table->index(['document_type', 'direction', 'status']);
            $table->index('number');
            $table->index('client_id');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
