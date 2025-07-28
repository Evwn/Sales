<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('variant_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_option_id');
            $table->string('value');
            $table->timestamps();
            $table->foreign('variant_option_id')->references('id')->on('variant_options')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('variant_values');
    }
}; 