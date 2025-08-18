<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
      public function up(): void
    {
        // Main Goods Receipt table
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            
            $table->string('reference')->unique(); // e.g., GR-00001
            $table->unsignedBigInteger('purchase_order_id')->nullable(); // Linked to PO
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('user_id'); // who received
            $table->date('receipt_date')->default(now()); // date of receipt
            $table->string('status')->default('pending'); // pending, completed, partial
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('purchase_order_id')->references('id')->on('purchases')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Goods Receipt Items table
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('goods_receipt_id'); 
            $table->unsignedBigInteger('purchase_order_item_id')->nullable(); // link to PO item if exists
            $table->unsignedBigInteger('item_id'); 
            $table->decimal('received_quantity', 15, 2)->default(0); 
            $table->decimal('accepted_quantity', 15, 2)->default(0); // if some rejected
            $table->decimal('rejected_quantity', 15, 2)->default(0); 
            $table->string('remarks')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('goods_receipt_id')->references('id')->on('goods_receipts')->onDelete('cascade');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_items')->onDelete('set null');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
    }
};
