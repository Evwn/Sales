<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_movement_analysis', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
        Schema::table('stock_valuation', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_movement_analysis', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('stock_valuation', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
    }
};