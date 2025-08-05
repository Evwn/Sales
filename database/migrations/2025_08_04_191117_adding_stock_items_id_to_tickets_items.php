<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pos_ticket_items', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_item_id')->nullable()->after('ticket_id');

            // If you want a foreign key constraint:
            $table->foreign('stock_item_id')
                ->references('id')->on('stock_items')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('pos_ticket_items', function (Blueprint $table) {
            $table->dropForeign(['stock_item_id']);
            $table->dropColumn('stock_item_id');
        });
    }
};