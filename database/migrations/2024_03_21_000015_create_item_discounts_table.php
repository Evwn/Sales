<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('discount_id');
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_discounts');
    }
}; 