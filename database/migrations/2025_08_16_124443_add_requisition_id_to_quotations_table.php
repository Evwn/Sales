<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Link to requisitions (assuming you already have a requisitions table)
            $table->unsignedBigInteger('requisition_id')->nullable()->after('id');

            $table->foreign('requisition_id')
                ->references('id')
                ->on('requisitions')
                ->onDelete('set null'); // don’t cascade delete since quotations may need history
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['requisition_id']);
            $table->dropColumn('requisition_id');
        });
    }
};
