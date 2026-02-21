<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add column without unique constraint
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('slug', 63)->after('id');
        });

        // 2. Populate slug from existing domains
        $domains = DB::table('domains')->get();
        foreach ($domains as $domain) {
            DB::table('tenants')
                ->where('id', $domain->tenant_id)
                ->update(['slug' => $domain->domain]);
        }

        // 3. Now add unique index (all rows have values)
        Schema::table('tenants', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
