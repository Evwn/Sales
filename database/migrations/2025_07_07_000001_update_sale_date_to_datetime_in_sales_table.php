<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dateTime('sale_date')->change();
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->date('sale_date')->change();
        });
    }
}; 