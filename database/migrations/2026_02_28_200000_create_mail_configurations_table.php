<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('host', 255)->nullable();
            $table->smallInteger('port')->unsigned()->default(587);
            $table->string('username', 255)->nullable();
            $table->text('password')->nullable(); // encrypted
            $table->enum('encryption', ['tls', 'ssl'])->nullable()->default('tls');
            $table->string('from_address', 255)->nullable();
            $table->string('from_name', 255)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('tested_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_configurations');
    }
};
