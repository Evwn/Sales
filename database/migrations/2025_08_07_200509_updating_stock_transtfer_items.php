<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            // Drop the foreign key and column for product_id
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');

            // Add stock_item_id and foreign key constraint
            $table->unsignedBigInteger('stock_item_id')->after('stock_transfer_id');
            $table->foreign('stock_item_id')
                  ->references('id')
                  ->on('stock_items')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            // Drop the foreign key and column for stock_item_id
            $table->dropForeign(['stock_item_id']);
            $table->dropColumn('stock_item_id');

            // Restore product_id and its foreign key
            $table->unsignedBigInteger('product_id')->after('stock_transfer_id');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onUpdate('no action')
                  ->onDelete('no action');
        });
    }
};
