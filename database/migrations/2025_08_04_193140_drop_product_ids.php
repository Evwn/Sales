<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_receipt_items', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('sales_receipt_items_product_id_foreign');
            $table->dropIndex('sales_receipt_items_sales_receipt_id_product_id_index');

            // Drop product_id column
            $table->dropColumn('product_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            // Drop foreign key first (exists in sale_items)
            $table->dropForeign('sale_items_product_id_foreign');
            $table->dropIndex('sale_items_product_id_index');

            // Drop product_id column
            $table->dropColumn('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_receipt_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->after('stock_item_id');

            // Recreate indexes
            $table->index('product_id', 'sales_receipt_items_product_id_foreign');
            $table->index(['sales_receipt_id', 'product_id'], 'sales_receipt_items_sales_receipt_id_product_id_index');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->after('stock_item_id');

            // Recreate index and foreign key
            $table->index('product_id', 'sale_items_product_id_index');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict')->onUpdate('no action');
        });
    }
};
