<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up()
    {

        Schema::create('quotation_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending','responded','declined'])->default('pending');
            $table->timestamps();
        });

        Schema::create('quotation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_supplier_id')->constrained('quotation_suppliers')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('unit_price', 15,2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotation_responses');
        Schema::dropIfExists('quotation_suppliers');

    }
};
