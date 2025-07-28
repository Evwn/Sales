<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->decimal('quantity', 15, 2)->default(0.00);
            $table->decimal('min_stock_level', 15, 2)->nullable();
            $table->decimal('max_stock_level', 15, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['location_id', 'item_id', 'variant_id'], 'stock_items_location_id_item_id_variant_id_unique');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('item_variants')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_items');
    }
}; 