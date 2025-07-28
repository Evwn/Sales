<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('unit_value', 10, 2)->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('upc')->nullable()->unique();
            $table->string('ean')->nullable()->unique();
            $table->string('isbn')->nullable()->unique();
            $table->string('mpn')->nullable()->unique();
            $table->enum('sold_by', ['each','weight','volume'])->default('each');
            $table->boolean('is_for_sale')->default(true);
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_composite')->default(false);
            $table->integer('in_stock')->default(0);
            $table->integer('low_stock')->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::dropIfExists('items');
    }
}; 