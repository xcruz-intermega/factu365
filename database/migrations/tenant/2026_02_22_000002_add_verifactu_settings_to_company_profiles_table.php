<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->boolean('verifactu_enabled')->default(false)->after('software_nif');
            $table->string('verifactu_environment', 20)->default('sandbox')->after('verifactu_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['verifactu_enabled', 'verifactu_environment']);
        });
    }
};
