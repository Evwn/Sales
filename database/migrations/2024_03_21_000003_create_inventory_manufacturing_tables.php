<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->string('short_code', 10);
                $table->string('description', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sku')->unique();
                $table->foreignId('category_id')->constrained('product_categories');
                $table->foreignId('unit_id')->constrained();
                $table->decimal('cost_price', 15, 2);
                $table->decimal('selling_price', 15, 2);
                $table->decimal('min_stock_level', 15, 2)->default(0);
                $table->decimal('max_stock_level', 15, 2)->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained();
                $table->enum('type', ['in', 'out']);
                $table->decimal('quantity', 15, 2);
                $table->string('reference');
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('product_categories');
    }
}; 