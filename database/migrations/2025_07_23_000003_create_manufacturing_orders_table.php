<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('manufacturing_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->string('reference')->unique();
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manufacturing_orders');
    }
}; 