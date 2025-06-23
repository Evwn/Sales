<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_commission', function (Blueprint $table) {
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sales_commission', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
        });
    }
}; 