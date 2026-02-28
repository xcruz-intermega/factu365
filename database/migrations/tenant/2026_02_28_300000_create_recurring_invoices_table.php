<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();

            $table->string('name', 200);
            $table->string('status', 20)->default('active')->comment('active, paused, finished');

            // Relations
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('series_id')->nullable()->constrained('document_series');
            $table->foreignId('payment_template_id')->nullable()->constrained('payment_templates')->nullOnDelete();

            // Invoice defaults
            $table->string('invoice_type', 5)->default('F1');
            $table->string('regime_key', 5)->default('01');
            $table->decimal('global_discount_percent', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('footer_text')->nullable();

            // Recurrence config
            $table->unsignedSmallInteger('interval_value')->default(1);
            $table->string('interval_unit', 10)->default('month')->comment('day, week, month, year');
            $table->date('start_date');
            $table->date('next_issue_date');
            $table->date('end_date')->nullable();
            $table->unsignedInteger('max_occurrences')->nullable();
            $table->unsignedInteger('occurrences_count')->default(0);

            // Behavior
            $table->boolean('auto_finalize')->default(false);
            $table->boolean('auto_send_email')->default(false);
            $table->string('email_recipients')->nullable()->comment('Override email addresses, comma separated');

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'next_issue_date']);
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};
