<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('supplier_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('supplier_name')->nullable()->comment('Free-text supplier for quick entry');
            $table->string('invoice_number')->nullable();
            $table->date('expense_date');
            $table->date('due_date')->nullable();
            $table->string('concept');
            $table->text('description')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(21.00);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('irpf_rate', 5, 2)->default(0);
            $table->decimal('irpf_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('expense_date');
            $table->index('payment_status');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
