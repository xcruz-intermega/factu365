<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->string('reference', 50)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(21.00);
            $table->string('exemption_code', 10)->nullable()->comment('Código exención IVA AEAT: E1-E6');
            $table->boolean('irpf_applicable')->default(false);
            $table->string('unit', 20)->default('unit')->comment('unit, hour, kg, m, etc.');
            $table->timestamps();
            $table->softDeletes();

            $table->index('reference');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
