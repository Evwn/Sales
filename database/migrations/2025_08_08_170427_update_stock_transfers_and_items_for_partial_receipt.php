<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up()
    {
        // 1. Add 'partially_received' to enum
        DB::statement("ALTER TABLE stock_transfers MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled', 'partially_received') NOT NULL DEFAULT 'pending'");

        // 2. Add 'quantity_received' column
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->decimal('quantity_received', 15, 2)->nullable()->after('quantity');
        });
    }

    public function down()
    {
        // Reverse the enum change
        DB::statement("ALTER TABLE stock_transfers MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        // Drop the added column
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropColumn('quantity_received');
        });
    }
};
