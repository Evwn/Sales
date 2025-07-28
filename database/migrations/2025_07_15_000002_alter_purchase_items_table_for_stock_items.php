<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Drop foreign key and column for product_id
            if (Schema::hasColumn('purchase_items', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            // Add stock_item_id
            $table->unsignedBigInteger('stock_item_id')->after('purchase_id');
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->onDelete('cascade');
            // Add variant_id (nullable)
            $table->unsignedBigInteger('variant_id')->nullable()->after('item_id');
            $table->foreign('variant_id')->references('id')->on('item_variants')->onDelete('set null');
        });
    }
    public function down()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Drop stock_item_id
            $table->dropForeign(['stock_item_id']);
            $table->dropColumn('stock_item_id');
            // Restore product_id
            $table->unsignedBigInteger('product_id')->after('purchase_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
        });
    }
}; 