<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_series', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 30)->comment('invoice, quote, delivery_note, proforma, receipt, rectificative, purchase_invoice');
            $table->string('prefix', 20)->comment('e.g. FACT-2025-');
            $table->unsignedBigInteger('next_number')->default(1);
            $table->unsignedTinyInteger('padding')->default(5)->comment('Zero-padding for number');
            $table->unsignedSmallInteger('fiscal_year');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['document_type', 'prefix']);
            $table->index(['document_type', 'fiscal_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_series');
    }
};
