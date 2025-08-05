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
        Schema::table('sale_items', function (Blueprint $table) {
            // Add the new stock_item_id column
            $table->unsignedBigInteger('stock_item_id')->nullable()->after('sale_id');
            
            // Add foreign key constraint for stock_item_id
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->onDelete('restrict');
            $table->index('stock_item_id');
        });
        
        // Now copy data from product_id to stock_item_id (if needed)
        // This would require additional logic to map products to stock items
        
        // Finally, drop the old product_id column and its constraints
        Schema::table('sale_items', function (Blueprint $table) {
            // Drop the old column (this will automatically drop the foreign key constraint)
            $table->dropColumn('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Add back the product_id column
            $table->unsignedBigInteger('product_id')->after('sale_id');
            
            // Add back the original foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
        });
        
        // Drop the new stock_item_id column and its constraints
        Schema::table('sale_items', function (Blueprint $table) {
            try {
                $table->dropForeign(['stock_item_id']);
            } catch (\Exception $e) {
                // Foreign key constraint doesn't exist, continue
            }
            
            $table->dropColumn('stock_item_id');
        });
    }
};
