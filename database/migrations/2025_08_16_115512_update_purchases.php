<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up() {
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('quotation_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('quotations')
                ->nullOnDelete();
        });
    }

    public function down() {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropColumn('quotation_id');
        });
    }
};
