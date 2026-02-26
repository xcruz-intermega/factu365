<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasury_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date')->index();
            $table->string('concept');
            $table->decimal('amount', 14, 2);
            $table->string('entry_type', 30)->index();
            $table->foreignId('document_due_date_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('bank_account_id');
            $table->index('document_due_date_id');
            $table->index('expense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_entries');
    }
};
