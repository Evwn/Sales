<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_type_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('location_type_id')->references('id')->on('location_types')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('business_id')->references('id')->on('businesses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('locations');
    }
}; 