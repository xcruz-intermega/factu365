<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('track_stock')->default(false)->after('unit');
            $table->decimal('stock_quantity', 12, 4)->default(0)->after('track_stock');
            $table->decimal('minimum_stock', 12, 4)->default(0)->after('stock_quantity');
            $table->boolean('allow_negative_stock')->default(true)->after('minimum_stock');
            $table->string('stock_mode', 20)->default('self')->after('allow_negative_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['track_stock', 'stock_quantity', 'minimum_stock', 'allow_negative_stock', 'stock_mode']);
        });
    }
};
