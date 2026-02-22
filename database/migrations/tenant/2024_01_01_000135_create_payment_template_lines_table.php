<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_template_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('days_from_issue');
            $table->decimal('percentage', 6, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_template_lines');
    }
};
