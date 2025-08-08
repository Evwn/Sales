<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['from_store_id']);
            $table->dropForeign(['to_store_id']);

            // Optionally rename columns
            $table->renameColumn('from_store_id', 'from_location_id');
            $table->renameColumn('to_store_id', 'to_location_id');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            // Add new foreign keys referencing locations
            $table->foreign('from_location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('to_location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Drop foreign keys to locations
            $table->dropForeign(['from_location_id']);
            $table->dropForeign(['to_location_id']);

            // Rename back to original
            $table->renameColumn('from_location_id', 'from_store_id');
            $table->renameColumn('to_location_id', 'to_store_id');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            // Re-add old foreign keys to stores
            $table->foreign('from_store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('to_store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }
};
