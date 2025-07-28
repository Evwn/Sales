<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('tax', 10, 2)->default(0);
                $table->string('status')->default('draft');
                $table->string('payment_status')->default('pending');
                $table->string('payment_method')->nullable();
                $table->date('sale_date');
                $table->string('barcode')->unique()->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('customer_id');
                $table->index('seller_id');
            });
        }

        if (!Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
                $table->decimal('quantity', 15, 2);
                $table->decimal('unit_price', 15, 2);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('tax', 15, 2)->default(0);
                $table->timestamps();
                $table->index('sale_id');
                $table->index('product_id');
            });
        }

        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->decimal('total_amount', 15, 2);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('tax', 15, 2)->default(0);
                $table->enum('status', ['draft', 'completed', 'cancelled'])->default('draft');
                $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
                $table->date('order_date')->nullable();
                $table->date('expected_date')->nullable();
                $table->text('notes')->nullable();
                $table->json('additional_costs')->nullable();
                $table->decimal('total_cost', 15, 2)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
                $table->index('supplier_id');
            });
        }

        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
                $table->decimal('quantity', 15, 2);
                $table->decimal('unit_price', 15, 2);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('tax', 15, 2)->default(0);
                $table->unsignedBigInteger('item_id');
                $table->integer('quantity_ordered')->default(0);
                $table->integer('quantity_received')->default(0);
                $table->decimal('purchase_cost', 15, 2)->nullable();
                $table->decimal('proportional_additional_cost', 15, 2)->nullable();
                $table->string('status')->default('pending');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
                $table->timestamps();
                $table->index('purchase_id');
                $table->index('product_id');
            });
        }

        if (!Schema::hasTable('sales_commission')) {
            Schema::create('sales_commission', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained('users')->onDelete('set null');
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->enum('status', ['pending', 'paid'])->default('pending');
                $table->timestamps();
                $table->softDeletes();
                $table->index('seller_id');
                $table->index('sale_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sales_commission');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
}; 