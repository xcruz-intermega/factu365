<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Description
            $table->string('concept');
            $table->text('description')->nullable();

            // Quantities & pricing
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('unit', 20)->default('unit');

            // Line discount
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);

            // Tax
            $table->decimal('vat_rate', 5, 2)->default(21.00);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->string('exemption_code', 10)->nullable();

            // IRPF retention
            $table->decimal('irpf_rate', 5, 2)->default(0);
            $table->decimal('irpf_amount', 14, 2)->default(0);

            // Surcharge (recargo equivalencia)
            $table->decimal('surcharge_rate', 5, 2)->default(0);
            $table->decimal('surcharge_amount', 14, 2)->default(0);

            // Computed totals
            $table->decimal('line_subtotal', 14, 2)->default(0)->comment('quantity * unit_price');
            $table->decimal('line_total', 14, 2)->default(0)->comment('After discount, before tax');

            $table->timestamps();

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_lines');
    }
};
