<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('discount_type', 20); // general, agreement, type, family
            $table->decimal('discount_percent', 6, 2);
            $table->string('product_type', 20)->nullable(); // product, service (for type discounts)
            $table->foreignId('product_family_id')->nullable()->constrained('product_families')->nullOnDelete();
            $table->decimal('min_amount', 14, 2)->nullable(); // for agreement discounts
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_discounts');
    }
};
