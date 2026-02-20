<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aeat_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoicing_record_id')->constrained()->cascadeOnDelete();
            $table->longText('request_xml')->nullable();
            $table->longText('response_xml')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->enum('result_status', [
                'pending',
                'accepted',
                'partially_accepted',
                'rejected',
                'error',
            ])->default('pending');
            $table->string('error_code')->nullable();
            $table->text('error_description')->nullable();
            $table->string('aeat_csv')->nullable();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->timestamps();

            $table->index('invoicing_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aeat_submissions');
    }
};
