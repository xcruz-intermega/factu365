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
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('rate', 5, 2)->unique();
            $table->decimal('surcharge_rate', 5, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_exempt')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed standard Spanish VAT rates
        DB::table('vat_rates')->insert([
            ['name' => 'General',        'rate' => 21.00, 'surcharge_rate' => 5.20, 'is_default' => true,  'is_exempt' => false, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reducido',       'rate' => 10.00, 'surcharge_rate' => 1.40, 'is_default' => false, 'is_exempt' => false, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Superreducido',  'rate' =>  4.00, 'surcharge_rate' => 0.50, 'is_default' => false, 'is_exempt' => false, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Exento',         'rate' =>  0.00, 'surcharge_rate' => 0.00, 'is_default' => false, 'is_exempt' => true,  'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_rates');
    }
};
