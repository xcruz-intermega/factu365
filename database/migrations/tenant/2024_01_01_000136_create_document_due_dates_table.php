<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_due_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->date('due_date');
            $table->decimal('amount', 14, 2);
            $table->decimal('percentage', 6, 2);
            $table->string('payment_status', 20)->default('pending');
            $table->date('payment_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['document_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_due_dates');
    }
};
