<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoicing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->enum('record_type', ['alta', 'anulacion'])->default('alta');

            // Hash chain fields
            $table->string('id_emisor_factura', 20)->comment('NIF del emisor');
            $table->string('num_serie_factura')->comment('Número serie factura');
            $table->string('fecha_expedicion', 10)->comment('DD-MM-YYYY');
            $table->string('tipo_factura', 5)->comment('F1, F2, R1, etc.');
            $table->string('cuota_total')->comment('Total VAT without trailing zeros');
            $table->string('importe_total')->comment('Total amount without trailing zeros');
            $table->string('previous_hash', 64)->default('')->comment('Hash del registro anterior');
            $table->string('hash', 64)->comment('SHA-256 hash of this record');
            $table->string('fecha_hora_generacion')->comment('ISO 8601 with timezone');

            // XML content
            $table->longText('xml_content')->nullable();

            // Submission tracking
            $table->enum('submission_status', [
                'pending',
                'submitted',
                'accepted',
                'rejected',
                'error',
            ])->default('pending');
            $table->string('aeat_csv')->nullable()->comment('CSV returned by AEAT');
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['document_id', 'record_type']);
            $table->index('submission_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoicing_records');
    }
};
