<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_variant_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_variant_id');
            $table->unsignedBigInteger('variant_value_id');
            $table->timestamps();
            $table->foreign('item_variant_id')->references('id')->on('item_variants')->onDelete('cascade');
            $table->foreign('variant_value_id')->references('id')->on('variant_values')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_variant_values');
    }
}; 