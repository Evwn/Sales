<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->string('upc')->unique()->nullable();
            $table->string('ean')->unique()->nullable();
            $table->string('isbn')->unique()->nullable();
            $table->string('mpn')->nullable(); // Manufacturer Part Number
            $table->string('unit')->nullable(); // e.g., ml, kg, pieces
            $table->decimal('unit_value', 10, 2)->nullable(); // e.g., 350 for 350ml
            $table->foreignId('created_by')->constrained('users'); // Track who added the item
            $table->foreignId('last_updated_by')->constrained('users'); // Track who last updated the item
            $table->timestamps();
            $table->softDeletes(); // Use soft deletes to prevent actual deletion
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
}; 