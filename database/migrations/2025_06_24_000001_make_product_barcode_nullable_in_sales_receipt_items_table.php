<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales_receipt_items', function (Blueprint $table) {
            $table->string('product_barcode')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('sales_receipt_items', function (Blueprint $table) {
            $table->string('product_barcode')->nullable(false)->change();
        });
    }
}; 