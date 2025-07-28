<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
}; 