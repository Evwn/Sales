<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up() {
        // Update quotations table
        Schema::table('quotations', function (Blueprint $table) {
            // Drop old foreign key + column
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');

            // Add supplier_id
            $table->foreignId('supplier_id')
                ->after('reference')
                ->constrained('suppliers')
                ->cascadeOnDelete();
        });

        // Update quotation_items table
        Schema::table('quotation_items', function (Blueprint $table) {
            // Drop old foreign key + column
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');

            // Add item_id
            $table->foreignId('item_id')
                ->after('quotation_id')
                ->constrained('items')
                ->restrictOnDelete();
        });
    }

    public function down() {
        // Rollback: restore customer_id and product_id
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
        });
    }
};
