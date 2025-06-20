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
        Schema::table('products', function (Blueprint $table) {
            // First check if the column exists
            if (Schema::hasColumn('products', 'sku')) {
                // Drop the unique index if it exists
                if (Schema::hasIndex('products', 'products_sku_unique')) {
                    $table->dropUnique('products_sku_unique');
                }
                // Then drop the column
                $table->dropColumn('sku');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable();
                $table->unique('sku', 'products_sku_unique');
            }
        });
    }
};
