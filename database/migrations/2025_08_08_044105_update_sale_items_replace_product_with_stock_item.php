<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {

            $table->dropIndex('sale_items_product_id_index');

            // Drop the column
            $table->dropColumn('product_id');

            // Add stock_item_id
            $table->foreignId('stock_item_id')
                ->after('sale_id')
                ->constrained('stock_items')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }



    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Reverse the changes if needed

            // Drop stock_item_id
            $table->dropForeign(['stock_item_id']);
            $table->dropColumn('stock_item_id');

            // Add product_id back
            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->restrictOnDelete();
            $table->index('product_id', 'sale_items_product_id_index');
        });
    }
};
