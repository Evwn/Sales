<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create a temporary table with the new schema
        Schema::create('products_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('buying_price', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Copy data from the old table to the new one
        DB::statement('INSERT INTO products_new (id, business_id, inventory_item_id, branch_id, price, buying_price, stock, status, created_at, updated_at) SELECT id, business_id, inventory_item_id, branch_id, price, buying_price, stock, status, created_at, updated_at FROM products');

        // Drop the old table
        Schema::drop('products');

        // Rename the new table to the original name
        Schema::rename('products_new', 'products');
    }

    public function down(): void
    {
        // Create a temporary table with the old schema
        Schema::create('products_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('buying_price', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('status')->default('active');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Copy data from the new table to the old one
        DB::statement('INSERT INTO products_old (id, business_id, inventory_item_id, branch_id, price, buying_price, stock, status, created_at, updated_at) SELECT id, business_id, inventory_item_id, branch_id, price, buying_price, stock, status, created_at, updated_at FROM products');

        // Drop the new table
        Schema::drop('products');

        // Rename the old table to the original name
        Schema::rename('products_old', 'products');
    }
}; 