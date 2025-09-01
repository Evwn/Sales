<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    Schema::create('supplier_responses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
        $table->decimal('total_amount', 15, 2);
        $table->text('notes')->nullable();
        $table->enum('status', ['submitted','pending'])->default('submitted');
        $table->timestamps();
    });

    Schema::create('supplier_response_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_response_id')->constrained('supplier_responses')->cascadeOnDelete();
        $table->foreignId('quotation_item_id')->constrained('items')->cascadeOnDelete();
        $table->decimal('unit_price', 15, 2);
        $table->integer('quantity');
        $table->decimal('line_total', 15, 2);
        $table->timestamps();
    });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_responses');
        Schema::dropIfExists('supplier_response_items');

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
