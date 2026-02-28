<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_invoice_id')->constrained('recurring_invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('concept');
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('unit', 20)->default('unidad');

            // Tax fields
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(21.00);
            $table->string('exemption_code', 10)->nullable();
            $table->decimal('irpf_rate', 5, 2)->default(0);
            $table->decimal('surcharge_rate', 5, 2)->default(0);

            $table->timestamps();

            $table->index('recurring_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_lines');
    }
};
