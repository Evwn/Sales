<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('component_item_id');
            $table->decimal('quantity', 10, 3);
            $table->decimal('cost', 10, 2)->nullable();
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('component_item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_components');
    }
}; 