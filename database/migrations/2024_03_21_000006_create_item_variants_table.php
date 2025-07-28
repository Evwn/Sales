<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->json('options');
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('upc')->nullable()->unique();
            $table->string('ean')->nullable()->unique();
            $table->string('isbn')->nullable()->unique();
            $table->string('mpn')->nullable()->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('in_stock')->default(0);
            $table->integer('low_stock')->nullable();
            $table->boolean('track_stock')->default(true);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_variants');
    }
}; 