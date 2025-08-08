<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        Schema::table('sales_receipt_items', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['product_id']);

            // Then drop the product-related columns
            if (Schema::hasColumn('sales_receipt_items', 'product_name')) {
                $table->dropColumn('product_name');
            }
            if (Schema::hasColumn('sales_receipt_items', 'product_barcode')) {
                $table->dropColumn('product_barcode');
            }

            // Add the new column
            $table->unsignedBigInteger('stock_item_id')->nullable()->after('sales_receipt_id');
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->onDelete('cascade');
            $table->index('stock_item_id');
            $table->index(['sales_receipt_id', 'stock_item_id']);
        });

        // Drop product_id column in a separate statement (after the FK is gone)
        Schema::table('sales_receipt_items', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_receipt_items', function (Blueprint $table) {
            // Add back the product_id column
            $table->unsignedBigInteger('product_id')->after('sales_receipt_id');
            
            // Add back the product_name and product_barcode columns
            $table->string('product_name');
            $table->string('product_barcode')->nullable();
            
            // Add back the original foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
        
        // Drop the new stock_item_id column and its constraints
        Schema::table('sales_receipt_items', function (Blueprint $table) {
            try {
                $table->dropForeign(['stock_item_id']);
            } catch (\Exception $e) {
                // Foreign key constraint doesn't exist, continue
            }
            
            $table->dropColumn('stock_item_id');
        });
    }
};
