<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->unsignedBigInteger('from_store_id');
            $table->unsignedBigInteger('to_store_id');
            $table->enum('status', ['pending','completed','cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('from_store_id')->references('id')->on('stores');
            $table->foreign('to_store_id')->references('id')->on('stores');
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 15, 2);
            $table->timestamps();

            $table->foreign('stock_transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
}; 