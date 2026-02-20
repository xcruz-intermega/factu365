<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('nif', 20);
            $table->string('address_street')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_postal_code', 10)->nullable();
            $table->string('address_province')->nullable();
            $table->string('address_country', 2)->default('ES');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_regime')->default('general');
            $table->decimal('irpf_rate', 5, 2)->default(15.00);
            $table->string('logo_path')->nullable();
            // VeriFactu
            $table->string('software_id')->nullable();
            $table->string('software_name')->default('Factu01');
            $table->string('software_version')->default('1.0');
            $table->string('software_nif')->nullable()->comment('NIF del fabricante SW');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
